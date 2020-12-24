<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Command\Participant\Remove;
use Proximum\Vimeet\Application\Exception\Participant\AlreadyLinkedToASheetOfThisEventException;
use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Application\Exception\Participant\Remove\ParticipantAttributedToProductCanNotBeRemovedException;
use Proximum\Vimeet\Application\Exception\Participant\Remove\ParticipantWithMeetingCanNotBeRemovedException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Event\ContactInfoGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Sheet\Participant\AddParticipantChecker;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AddType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\RemoveType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SheetParticipantController extends Controller
{
    /**
     * Render the form of the addition of a participant. Loaded by ajax from the sheet.
     *
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param string      $locale
     * @param string      $key
     * @param UserDomain  $userDomain
     *
     * @return Response
     */
    public function addParticipantAction(EventDomain $eventDomain, Sheet $sheet, $locale, $key, UserDomain $userDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!$this->get(AddParticipantChecker::class)->canAddParticipant($sheet)) {
            throw $this->createNotFoundException(
                sprintf('This sheet %s can not buy anymore participant', $sheet->getId())
            );
        }

        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);

        try {
            $object = $templateData->getObject($key);
        } catch (Template\Exception\ObjectNotFoundException $exception) {
            throw $this->createNotFoundException(sprintf('The given key %s is not found', $key));
        }

        if (!$object->isParticipant()) {
            throw $this->createNotFoundException(sprintf('The given object %s is not a participant', $key));
        }

        $participantProductViews = $this->get('tactician.commandbus.query')->handle(
            new ParticipantProductViewQuery($sheet, $locale)
        );

        $addParticipant = new Add(
            $sheet,
            $locale,
            $userDomain->getUser(),
            $participantProductViews
        );
        $form = $this->createForm(AddType::class, $addParticipant, [
            'sheet'    => $sheet,
            'products' => $participantProductViews,
            'locale'   => $locale,
            'action'   => $this->generateUrl(
                'event_sheet_handle_participant',
                ['sheet' => $sheet->getId(), 'locale' => $locale, 'key' => $key]
            ),
        ]);

        return $this->render('EventBundle:Participant:add.html.twig', [
            'uid'                     => $key,
            'form'                    => $form->createView(),
            'sheet'                   => $sheet,
            'participantProductViews' => $participantProductViews,
            'backRoute'               => 'backToSheet',
        ]);
    }

    /**
     * Add a participant and display the sheet with the modal in case of form error.
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param string      $locale
     * @param string      $key
     * @param UserDomain  $userDomain
     *
     * @return Response
     */
    public function handleAddParticipantAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        $locale,
        $key,
        UserDomain $userDomain
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $user = $userDomain->getUser();

        $canAddParticipant = $this->get(AddParticipantChecker::class)->canAddParticipant($sheet);
        if (!$canAddParticipant) {
            throw $this->createNotFoundException(
                sprintf('This sheet %s can not buy anymore participant', $sheet->getId())
            );
        }

        $participantProductViews = $this->get('tactician.commandbus.query')->handle(
            new ParticipantProductViewQuery($sheet, $locale)
        );

        $addParticipant = new Add(
            $sheet,
            $locale,
            $user,
            $participantProductViews
        );
        $form  = $this->createForm(AddType::class, $addParticipant, [
            'sheet'    => $sheet,
            'locale'   => $locale,
            'products' => $participantProductViews,
            'action'   => $this->generateUrl(
                'event_sheet_handle_participant',
                ['sheet' => $sheet->getId(), 'locale' => $locale, 'key' => $key]
            ),
        ]);

        // Handle the form, update the object and redirect to the sheet if valid
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('vimeet_infrastructure.vimeet.application.command.participant.add_handler')->handle($addParticipant);

                return $this->redirectToRoute('event_sheet_locale', ['sheet' => $sheet->getId(), 'locale' => $locale]);
            } catch (AlreadyLinkedToASheetOfThisEventException $exception) {
                $form->get('email')->addError(new FormError('validators.participant.alreadyLinkedToASheet'));
            } catch (ParticipantAlreadyExistException $exception) {
                $form->get('email')->addError(new FormError('validators.participant.alreadyLinkedToThisSheet'));
            }
        }

        // If the form is not valid, render the sheet and force the popin with the participant form
        list($nomenclatures, $participants, $taggedData) = $this->get('sheet.infos_helper')->getInfos(
            $sheet,
            $user,
            $locale,
            false
        );
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object       = $this->getParticipantObject($templateData, $key);
        $label        = $object->getLabel($locale, $sheet->getEvent()->getFallback());

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_SHEET,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'canAddParticipant'       => $canAddParticipant,
            'event'                   => $eventDomain->getEvent(),
            'form_participant'        => $form->createView(),
            'isRequestMeetingEnabled' => false,
            'label'                   => $label,
            'locale'                  => $locale,
            'nomenclatures'           => $nomenclatures,
            'participants'            => $participants,
            'sheet'                   => $sheet,
            'taggedData'              => $taggedData,
            'templateData'            => $templateData,
            'tipTranslationViews'     => $tipTranslationViews,
            'uid'                     => $key,
            'participantProductViews' => $participantProductViews,
        ]);
    }

    /**
     * Render the form to remove participant. Loaded by ajax from the sheet.
     *
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param string      $locale
     * @param string      $key
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function removeParticipantAction(EventDomain $eventDomain, Sheet $sheet, $locale, $key): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        list($form) = $this->removeParticipantData($sheet, $locale, $key);

        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);

        try {
            $object = $templateData->getObject($key);
        } catch (Template\Exception\ObjectNotFoundException $exception) {
            throw $this->createNotFoundException(sprintf('The given key %s is not found', $key));
        }

        if (!$object->isParticipant()) {
            throw $this->createNotFoundException(sprintf('The given object %s is not a participant', $key));
        }

        $label             = $object->getLabel($locale, $sheet->getEvent()->getFallback());
        $cardListViewQuery = new CardListViewQuery($sheet, $this->getUser(), $locale, false);
        $participants      = $this->get('tactician.commandbus.query')->handle($cardListViewQuery);

        return $this->render('EventBundle:Participant:remove.html.twig', [
            'uid'          => $key,
            'form'         => $form->createView(),
            'label'        => $label,
            'participants' => $participants,
        ]);
    }

    /**
     * Remove a participant and display the sheet with the modal in case of form error.
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param UserDomain  $userDomain
     * @param Sheet       $sheet
     * @param string      $locale
     * @param string      $key
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function handleRemoveParticipantAction(Request $request, EventDomain $eventDomain, UserDomain $userDomain, Sheet $sheet, $locale, $key): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $user = $userDomain->getUser();

        list($form, $remove) = $this->removeParticipantData($sheet, $locale, $key);

        // Handle the form, update the object and redirect to the sheet if valid
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($remove);

                return $this->redirectToRoute(
                    'event_sheet_locale',
                    ['sheet' => $sheet->getId(), 'locale' => $locale]
                );
            } catch (CanNotRemoveAllParticipantsException $exception) {
                $form->addError(new FormError('validators.participant.canNotRemoveAllParticipants'));
            } catch (ParticipantAttributedToProductCanNotBeRemovedException $exception) {
                $form->addError(
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
                $form->addError(
                    new FormError(
                        $this->get('translator')->transChoice(
                            'validators.participant.remove.hasMeeting',
                            $exception->countParticipants(),
                            [
                                '%participantName%' => $exception->getParticipantNames(),
                                '%contactInfo%'     => ContactInfoGuesser::getContactInfos($eventDomain->getEvent()),
                            ],
                            'validators'
                        )
                    )
                );
            }
        }

        // If the form is not valid, render the sheet and force the popin with the remove participant form
        list($nomenclatures, $participants, $taggedData) = $this->get('sheet.infos_helper')->getInfos(
            $sheet,
            $this->getUser(),
            $locale,
            false
        );
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object       = $this->getParticipantObject($templateData, $key);
        $label        = $object->getLabel($locale, $sheet->getEvent()->getFallback());

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_SHEET,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);
        $canAddParticipant = $this->get(AddParticipantChecker::class)->canAddParticipant($sheet);

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'canAddParticipant'       => $canAddParticipant,
            'event'                   => $eventDomain->getEvent(),
            'form_remove'             => $form->createView(),
            'isRequestMeetingEnabled' => false,
            'label'                   => $label,
            'locale'                  => $locale,
            'nomenclatures'           => $nomenclatures,
            'participants'            => $participants,
            'sheet'                   => $sheet,
            'taggedData'              => $taggedData,
            'templateData'            => $templateData,
            'tipTranslationViews'     => $tipTranslationViews,
            'uid'                     => $key,
        ]);
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     * @param string $key
     *
     * @return array
     */
    private function removeParticipantData(Sheet $sheet, $locale, $key): array
    {
        if (1 === $sheet->countParticipants()) {
            throw $this->createNotFoundException('Impossible to remove participants from a sheet with one participant');
        }

        $remove = new Remove($sheet, $locale);
        $form   = $this->createForm(RemoveType::class, $remove, [
            'action' => $this->generateUrl(
                'event_sheet_handle_remove_participant',
                ['sheet' => $sheet->getId(), 'locale' => $locale, 'key' => $key]
            ),
            'participants' => $sheet->getParticipants(),
        ]);

        return [
            $form,
            $remove,
        ];
    }

    /**
     * @param Template\TemplateData $templateData
     * @param string                $key
     *
     * @throws NotFoundHttpException
     *
     * @return Template\TemplateObject
     */
    private function getParticipantObject(Template\TemplateData $templateData, $key): Template\TemplateObject
    {
        try {
            $object = $templateData->getObject($key);
        } catch (Template\Exception\ObjectNotFoundException $exception) {
            throw $this->createNotFoundException(sprintf('The given key %s is not found', $key));
        }

        if (!$object->isParticipant()) {
            throw $this->createNotFoundException(sprintf('The given object %s is not a participant', $key));
        }

        return $object;
    }
}
