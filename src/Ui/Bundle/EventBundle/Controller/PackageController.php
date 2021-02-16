<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Package\PromotionCode\Remove;
use Proximum\Vimeet\Application\Command\Package\Step\AbstractStep;
use Proximum\Vimeet\Application\Command\Participant\Add as AddParticipant;
use Proximum\Vimeet\Application\Command\Participant\Remove as RemoveParticipant;
use Proximum\Vimeet\Application\Exception\Participant\AlreadyLinkedToASheetOfThisEventException;
use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Application\Exception\Participant\Remove\ParticipantAttributedToProductCanNotBeRemovedException;
use Proximum\Vimeet\Application\Exception\Participant\Remove\ParticipantWithMeetingCanNotBeRemovedException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Application\Query\Package\PackageViewQuery;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Event\ContactInfoGuesser;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Package\Funnel\Step as FunnelStep;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\OptionsType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\ParticipantAndPlanningType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\PlansType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AddType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\RemoveType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PackageController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return RedirectResponse
     */
    public function redirectAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $sheet = $this->get('sheet.sheet_guesser')
            ->getUserSheet($this->getUser(), $eventDomain->getEvent(), $request->getLocale());

        return $this->redirectToRoute('event_package_redirect_depending_on_context', ['sheet' => $sheet->getId()]);
    }

    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return RedirectResponse
     */
    public function redirectDependingOnContextAction(EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!empty($sheet->getNotCancelledOrders())) {
            return $this->redirectToRoute('event_order_summary_total', [
                'sheet' => $sheet->getId(),
            ]);
        }

        return $this->redirectToRoute('event_package_step', [
            'sheet' => $sheet->getId(),
            'step'  => 1,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param int         $step
     * @param UserDomain  $userDomain
     *
     * @return RedirectResponse|Response
     */
    public function stepAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        int $step,
        UserDomain $userDomain
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->authorizeAccess($eventDomain, $sheet);

        $funnel = $this->get('package.funnel.funnel_factory')->create($sheet, $request->getLocale());

        if (!$funnel->hasStep($step)) {
            throw $this->createNotFoundException(
                sprintf(
                    'Unkown %s step for package of sheet %s',
                    $step,
                    $sheet->getId()
                )
            );
        }

        $currentStep = $funnel->getStep($step);

        if (!$funnel->isStepAvailable($currentStep)) {
            return $this->redirectToRoute(
                'event_package_step',
                [
                    'sheet' => $sheet->getId(),
                    'step'  => $funnel->getCurrentUncompletedStep()->index,
                ]
            );
        }

        $command = $this->get('components.step.step_command_factory')
            ->create($currentStep->type, $sheet, $currentStep->index);

        $form = $this->stepTypeAssociatedForm($currentStep->type, $command, $step, $request->getLocale());

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);

            $nextStep = $funnel->getNextStep($step);

            if (null !== $nextStep) {
                return $this->redirectToRoute(
                    'event_package_step',
                    ['sheet' => $sheet->getId(), 'step' => $nextStep->index]
                );
            }

            return $this->redirectToRoute('event_package_summary', [
                'sheet' => $sheet->getId(),
            ]);
        }

        $displayAddParticipantForm    = false;
        $displayRemoveParticipantForm = false;
        $form_add                     = null;
        $form_remove                  = null;
        $participants                 = [];
        $participantProductViews      = [];

        if (FunnelStep::TYPE_PARTICIPANT_PLANNING === $currentStep->type) {
            $participantProductViews = $this->get('tactician.commandbus.query')->handle(
                new ParticipantProductViewQuery($sheet, $request->getLocale())
            );

            list(
                $displayAddParticipantForm,
                $displayRemoveParticipantForm,
                $form_add,
                $form_remove,
                $participants,
                $redirect
            ) = $this->handleStepParticipant($request, $sheet, $step, $userDomain->getUser(), $participantProductViews);

            if ($redirect) {
                return $this->redirectToRoute('event_package_step', [
                    'sheet' => $sheet->getId(),
                    'step'  => $step,
                ]);
            }
        }

        $view = $this->get('query.package.package_view_query_handler')->handle(
            new PackageViewQuery(
                $funnel,
                $currentStep,
                $sheet,
                $request->getLocale()
            )
        );

        $user = $userDomain->getUser();
        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_PACKAGE,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);

        return $this->render('EventBundle:Package:step.html.twig', [
            'event'                        => $eventDomain->getEvent(),
            'sheet'                        => $sheet,
            'view'                         => $view,
            'form'                         => $form->createView(),
            'form_add'                     => null !== $form_add ? $form_add->createView() : $form_add,
            'form_remove'                  => null !== $form_remove ? $form_remove->createView() : $form_remove,
            'displayAddParticipantForm'    => $displayAddParticipantForm,
            'displayRemoveParticipantForm' => $displayRemoveParticipantForm,
            'participants'                 => $participants,
            'participantProductViews'      => $participantProductViews,
            'tipTranslationViews'          => $tipTranslationViews,
        ]);
    }

    /**
     * @param Request $request
     * @param Sheet   $sheet
     * @param int     $step
     * @param User    $user
     * @param array   $participantProductViews
     *
     * @return array|RedirectResponse
     */
    private function handleStepParticipant(Request $request, Sheet $sheet, $step, User $user, array $participantProductViews)
    {
        $locale = $request->getLocale();
        $displayAddParticipantForm    = false;
        $displayRemoveParticipantForm = false;
        $redirect                     = false;

        $addParticipant = new AddParticipant(
            $sheet,
            $locale,
            $user,
            $participantProductViews
        );
        $form_add       = $this->createForm(AddType::class, $addParticipant, [
            'sheet'    => $sheet,
            'locale'   => $locale,
            'products' => $participantProductViews,
            'action'   => $this->generateUrl('event_package_step', [
                'sheet' => $sheet->getId(),
                'step'  => $step,
            ]),
        ]);

        $removeParticipant = new RemoveParticipant($sheet, $locale);
        $form_remove       = $this->createForm(RemoveType::class, $removeParticipant, [
            'action'       => $this->generateUrl('event_package_step', [
                'sheet' => $sheet->getId(),
                'step'  => $step,
            ]),
            'participants' => $sheet->getParticipants(),
        ]);

        if ($form_add->handleRequest($request)->isSubmitted()) {
            if ($form_add->isValid()) {
                try {
                    $this->get('tactician.commandbus')->handle($addParticipant);

                    $redirect = true;
                } catch (AlreadyLinkedToASheetOfThisEventException $exception) {
                    $form_add->get('email')->addError(new FormError('validators.participant.alreadyLinkedToASheet'));
                } catch (ParticipantAlreadyExistException $exception) {
                    $form_add->get('email')->addError(new FormError('validators.participant.alreadyLinkedToThisSheet'));
                }
            }

            $displayAddParticipantForm = true;
        }

        if ($form_remove->handleRequest($request)->isSubmitted()) {
            if ($form_remove->isValid()) {
                try {
                    $this->get('command.participant.remove_handler')->handle($removeParticipant);

                    $redirect = true;
                } catch (CanNotRemoveAllParticipantsException $exception) {
                    $form_remove->addError(new FormError('validators.participant.canNotRemoveAllParticipants'));
                } catch (ParticipantAttributedToProductCanNotBeRemovedException $exception) {
                    $form_remove->addError(
                        new FormError(
                            $this->get('translator')->transChoice(
                                'validators.participant.remove.hasAttributedProduct',
                                $exception->countParticipants(),
                                [
                                    '%participantName%' => $exception->getParticipantNames(),
                                ],
                                'validators'
                            )
                        )
                    );
                } catch (ParticipantWithMeetingCanNotBeRemovedException $exception) {
                    $form_remove->addError(
                        new FormError(
                            $this->get('translator')->transChoice(
                                'validators.participant.remove.hasMeeting',
                                $exception->countParticipants(),
                                [
                                    '%participantName%' => $exception->getParticipantNames(),
                                    '%contactInfo%'     => ContactInfoGuesser::getContactInfos($sheet->getEvent()),
                                ],
                                'validators'
                            )
                        )
                    );
                }
            }

            $displayRemoveParticipantForm = true;
        }

        $cardListViewQuery = new CardListViewQuery($sheet, $this->getUser(), $locale, false);
        $participants      = $this->get('query.participant.card_list_view_query_handler')->handle($cardListViewQuery);

        return [
            $displayAddParticipantForm,
            $displayRemoveParticipantForm,
            $form_add,
            $form_remove,
            $participants,
            $redirect,
        ];
    }

    /**
     * @param string       $type
     * @param AbstractStep $command
     * @param int          $step
     * @param string       $locale
     *
     * @throws \InvalidArgumentException
     *
     * @return FormInterface
     */
    private function stepTypeAssociatedForm(
        string $type,
        AbstractStep $command,
        int $step,
        string $locale
    ): FormInterface {
        $action = $this->generateUrl('event_package_step', ['sheet' => $command->sheet->getId(), 'step' => $step]);

        if (FunnelStep::TYPE_PLAN === $type) {
            return $this->createForm(PlansType::class, $command, [
                'action' => $action,
                'sheet' => $command->sheet,
            ]);
        }

        if (FunnelStep::TYPE_PARTICIPANT_PLANNING === $type) {
            return $this->createForm(ParticipantAndPlanningType::class, $command, [
                'action' => $action,
                'sheet' => $command->sheet,
                'locale' => $locale,
            ]);
        }

        if (FunnelStep::TYPE_OPTIONS === $type) {
            return $this->createForm(OptionsType::class, $command, [
                'action' => $action,
                'sheet' => $command->sheet,
                'locale' => $locale,
            ]);
        }

        throw new \InvalidArgumentException(sprintf('Form Package Step type %s not implemented', $type));
    }

    /**
     * @param EventDomain      $eventDomain
     * @param Sheet            $sheet
     * @param PromotionCodeRow $promotionCodeRow
     *
     * @return RedirectResponse
     */
    public function removePromotionCodeAction(
        EventDomain $eventDomain,
        Sheet $sheet,
        PromotionCodeRow $promotionCodeRow
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->authorizeAccess($eventDomain, $sheet);

        $remove = new Remove($sheet, $promotionCodeRow);
        $this->get('tactician.commandbus')->handle($remove);
        $this->addFlash('success', 'flash.package.promotion.delete.success');

        return $this->redirectToRoute('event_package_summary', ['sheet' => $sheet->getId()]);
    }

    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return RedirectResponse
     */
    public function fillBillingInfoAction(EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->authorizeAccess($eventDomain, $sheet);

        $this->addFlash('package_complete_billing_info', $sheet->getId());
        $this->addFlash('package_funnel_billing_info', $sheet->getId());

        return $this->redirectToRoute('event_billing_info', [
            'sheet' => $sheet->getId(),
        ]);
    }

    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     */
    private function authorizeAccess(EventDomain $eventDomain, Sheet $sheet)
    {
        if ($sheet->getEvent() !== $eventDomain->getEvent()) {
            throw $this->createNotFoundException(
                sprintf(
                    'Sheet %s not present on Event %s',
                    $sheet->getId(),
                    $eventDomain->getEvent()->getId()
                )
            );
        }

        if (!$sheet->getPackage()->isPassable()) {
            throw $this->createNotFoundException(
                sprintf('Package for sheet %s is not passable', $sheet->getId())
            );
        }
    }
}
