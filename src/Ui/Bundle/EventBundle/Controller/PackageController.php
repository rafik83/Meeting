<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Package\PromotionCode\Add;
use Proximum\Vimeet\Application\Command\Package\PromotionCode\Remove;
use Proximum\Vimeet\Application\Command\Package\Step\AbstractStep;
use Proximum\Vimeet\Application\Command\Participant\Add as AddParticipant;
use Proximum\Vimeet\Application\Command\Participant\Remove as RemoveParticipant;
use Proximum\Vimeet\Application\Command\Participant\RemoveResult;
use Proximum\Vimeet\Application\Exception\Participant\AlreadyLinkedToASheetOfThisEventException;
use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Application\Query\Package\PackageViewQuery;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\SummaryViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\Step as FunnelStep;
use Proximum\Vimeet\Domain\Package\Summary\PromotionCode;
use Proximum\Vimeet\Domain\Package\Summary\TermsOfSale;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeException;
use Proximum\Vimeet\Domain\Event\ContactInfoGuesser;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\OptionsType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\ParticipantAndPlanningType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\PlansType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\Summary\PromotionCodeType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\Summary\TermsOfSaleType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AddType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\RemoveType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
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
     *
     * @return RedirectResponse|Response
     */
    public function stepAction(Request $request, EventDomain $eventDomain, Sheet $sheet, int $step)
    {
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

        if ($currentStep->type === FunnelStep::TYPE_PARTICIPANT_PLANNING) {
            list (
                $displayAddParticipantForm,
                $displayRemoveParticipantForm,
                $form_add,
                $form_remove,
                $participants,
                $redirect
            ) = $this->handleStepParticipant($request, $sheet, $step);

            if ($redirect) {
                return $this->redirectToRoute('event_package_step', [
                    'sheet' => $sheet->getId(),
                    'step'  => $step,
                ]);
            }
        }

        $view = $this->get('tactician.commandbus.query')->handle(
            new PackageViewQuery(
                $funnel,
                $currentStep,
                $sheet,
                $request->getLocale()
            )
        );

        $participantProductView = $this->get('tactician.commandbus.query')->handle(
            new ParticipantProductViewQuery($sheet, $request->getLocale())
        );

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
            'participantProductView'       => $participantProductView,
        ]);
    }

    /**
     * @param Request     $request
     * @param Sheet       $sheet
     * @param int         $step
     *
     * @return array|RedirectResponse
     */
    private function handleStepParticipant(Request $request, Sheet $sheet, $step)
    {
        $locale = $request->getLocale();
        $displayAddParticipantForm    = false;
        $displayRemoveParticipantForm = false;
        $redirect                     = false;

        $addParticipant = new AddParticipant($sheet, $locale, $this->getUser());
        $form_add       = $this->createForm(AddType::class, $addParticipant, [
            'sheet'  => $sheet,
            'locale' => $locale,
            'action' => $this->generateUrl('event_package_step', [
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

        if ($form_add->handleRequest($request)->isSubmitted() && $form_add->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($addParticipant);

                $redirect = true;
            } catch (AlreadyLinkedToASheetOfThisEventException $exception) {
                $form_add->get('email')->addError(new FormError('validators.participant.alreadyLinkedToASheet'));
            } catch (ParticipantAlreadyExistException $exception) {
                $form_add->get('email')->addError(new FormError('validators.participant.alreadyLinkedToThisSheet'));
            }

            $displayAddParticipantForm = true;
        }

        if ($form_remove->handleRequest($request)->isSubmitted() && $form_remove->isValid()) {
            try {
                /** @var RemoveResult $result */
                $result = $this->get('tactician.commandbus')->handle($removeParticipant);

                if (!$result->hasParticipantWithMeeting()) {
                    $redirect = true;
                } else {


                    $form_remove->addError(
                        new FormError(
                            $this->get('translator')->transChoice(
                                'validators.participant.remove.hasMeeting',
                                $result->countParticipants(),
                                ['%participantName%' => $result->getParticipantsName(), '%contactInfo%' => ContactInfoGuesser::getContactInfos($sheet->getEvent())], 'validators'
                            )
                        )
                    );
                }
            } catch (CanNotRemoveAllParticipantsException $exception) {
                $form_remove->addError(new FormError('validators.participant.canNotRemoveAllParticipants'));
            }

            $displayRemoveParticipantForm = true;
        }

        $cardListViewQuery = new CardListViewQuery($sheet, $this->getUser(), $locale, false);
        $participants      = $this->get('tactician.commandbus.query')->handle($cardListViewQuery);

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
     * @return FormInterface
     * @throw \InvalidArgumentException
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
            ]);
        }

        throw new \InvalidArgumentException(sprintf('Form Package Step type %s not implemented', $type));
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return RedirectResponse|Response
     */
    public function summaryAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->authorizeAccess($eventDomain, $sheet);

        $this->get('cart_cleaner')->handle($sheet);
        $funnel = $this->get('package.funnel.funnel_factory')->create($sheet, $request->getLocale());

        if (!$funnel->isCompleted()) {
            return $this->redirectToRoute('event_package_step', [
                'sheet' => $sheet->getId(),
                'step'  => (null !== $funnel->getCartStep()) ? $funnel->getCartStep()->getCurrentStep() : 1,
            ]);
        }

        $billingInfo = $this->get('repository.billing_info_repository')->getBySheet($sheet);

        // Redirect to the billing info action if the billing info are not completed
        if (null === $billingInfo || !$billingInfo->isCompleted()) {
            $this->addFlash('package_complete_billing_info', $sheet->getId());
            $this->addFlash('package_funnel_billing_info', true);

            return $this->redirectToRoute('event_billing_info', [
                'sheet' => $sheet->getId(),
            ]);
        }

        $termsOfSale     = new TermsOfSale();
        $formTermsOfSale = $this->createForm(TermsOfSaleType::class, $termsOfSale);

        $promotionCode     = new PromotionCode();
        $formPromotionCode = $this->createForm(PromotionCodeType::class, $promotionCode);

        if ($formTermsOfSale->handleRequest($request)->isSubmitted() && $formTermsOfSale->isValid()) {
            $this->addFlash('package_completed_payment', $sheet->getId());

            return $this->redirectToRoute('event_package_payment', [
                'sheet' => $sheet->getId(),
            ]);
        }

        if ($formPromotionCode->handleRequest($request)->isSubmitted() && $formPromotionCode->isValid()) {
            $this->validatePromotionCode($sheet, $promotionCode);

            return $this->redirect($this->generateUrl('event_package_summary', ['sheet' => $sheet->getId()]) . '#summary-promo-code-row');
        }

        $view = $this->get('tactician.commandbus.query')->handle(
            new SummaryViewQuery(
                $sheet,
                $funnel,
                $funnel->getCart(),
                $request->getLocale()
            )
        );

        return $this->render('EventBundle:Package:summary.html.twig', [
            'event'             => $eventDomain->getEvent(),
            'formTermsOfSale'   => $formTermsOfSale->createView(),
            'formPromotionCode' => $formPromotionCode->createView(),
            'sheet'             => $sheet,
            'view'              => $view,
        ]);
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
     * @param Sheet         $sheet
     * @param PromotionCode $promotionCode
     */
    private function validatePromotionCode(Sheet $sheet, PromotionCode $promotionCode)
    {
        $command = new Add($sheet, $promotionCode->promotionCode);

        try {
            $this->get('tactician.commandbus')->handle($command);
        } catch (PromotionCodeException $exception) {
            $this->addFlash('package_promotion_code_error', $exception->getFlash());
        }
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
                sprintf('Sheet %s not present on Event %s')
            );
        }

        if (!$sheet->getPackage()->isPassable()) {
            throw $this->createNotFoundException(
                sprintf('Package for sheet %s is not passable', $sheet->getId())
            );
        }
    }
}
