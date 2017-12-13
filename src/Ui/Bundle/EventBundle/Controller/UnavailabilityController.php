<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Unavailability\Remove;
use Proximum\Vimeet\Application\Command\User\Availability\Confirmation;
use Proximum\Vimeet\Application\Exception\Unavailability\CanNotDeleteUnavailabilityException;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AgendaAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\Availability\ConfirmationType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Unavailability\CreateForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Unavailability\CreateFormView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class UnavailabilityController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Participant $participant
     * @param Sheet       $sheet
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, EventDomain $eventDomain, Participant $participant, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::UNAVAILABILITY_ADD, $sheet);
        $this->denyAccessUnlessGranted(AgendaAccessVoter::PERMISSION, $eventDomain->getEvent());
        $this->checkSheetHasParticipant($sheet, $participant);

        $event = $eventDomain->getEvent();
        $user  = $this->getUser();

        $actionUrl = $this->generateUrl('event_unavailability_create', [
            'participant' => $participant->getId(),
            'sheet'       => $sheet->getId(),
        ]);

        /** @var CreateFormView $createFormView */
        $createFormView = $this->get('handler.unavailability.create_form_handler')->handle(
            new CreateForm($request, $event, $sheet, $user, $actionUrl)
        );

        if ($createFormView->isXmlHttpRequest()) {
            return $this->render('EventBundle:Unavailability:create-form.html.twig', [
                'form_unavailability' => $createFormView->formView,
            ]);
        }

        if ($createFormView->isSuccess()) {
            return  $this->redirectToRoute('event_agenda_participant', [
                'participant' => $participant->getId(),
                'sheet'       => $sheet->getId(),
            ]);
        }

        /** @var AgendaView $agenda */
        $agenda = $this
            ->get('tactician.commandbus.query')
            ->handle(
                new AgendaViewQuery($event, $sheet, $participant, $request->getLocale(), $user)
            );

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet->getType(),
            TipTranslationViewQueryHandler::CONTEXT_AGENDA,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);

        return $this->render('EventBundle:Unavailability:create.html.twig', [
            'event'               => $event,
            'agenda'              => $agenda,
            'sheet'               => $sheet,
            'form_unavailability' => $createFormView->formView,
            'tipTranslationViews' => $tipTranslationViews,
        ]);
    }

    /**
     * @param Request       $request
     * @param EventDomain   $eventDomain
     * @param Sheet         $sheet
     * @param UserInterface $user
     *
     * @return Response
     */
    public function createFromConfirmationAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        UserInterface $user
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::UNAVAILABILITY_ADD, $sheet);
        $this->denyAccessUnlessGranted(AgendaAccessVoter::PERMISSION, $eventDomain->getEvent());

        $event = $eventDomain->getEvent();

        $actionUrl = $this->generateUrl('event_unavailability_create_from_confirmation', [
            'sheet' => $sheet->getId(),
        ]);

        /** @var CreateFormView $createFormView */
        $createFormView = $this->get('tactician.commandbus')->handle(
            new CreateForm($request, $event, $sheet, $user, $actionUrl)
        );

        if ($createFormView->isXmlHttpRequest()) {
            return $this->render('EventBundle:Unavailability:create-form.html.twig', [
                'form_unavailability' => $createFormView->formView,
            ]);
        }

        if ($createFormView->isSuccess()) {
            $this->addFlash('success', 'flash.unavailability.add.success');
            return  $this->redirectToRoute('event_availability_confirmation', [
                'sheet' => $sheet->getId(),
            ]);
        }

        $availabilityConfirmation = new Confirmation($eventDomain->getEvent(), $user);
        $form = $this->createForm(ConfirmationType::class, $availabilityConfirmation, [
            'action' => $this->generateUrl('event_availability_confirmation', [
                'sheet' => $sheet->getId(),
            ])
        ]);

        return $this->render('EventBundle:Availability:confirmation.html.twig', [
            'event'               => $event,
            'form'                => $form->createView(),
            'form_unavailability' => $createFormView->formView,
            'sheet'               => $sheet,
        ]);
    }

    /**
     * @param EventDomain    $eventDomain
     * @param Unavailability $unavailability
     * @param Participant    $participant
     * @param Sheet          $sheet
     *
     * @return RedirectResponse
     */
    public function removeAction(
        EventDomain $eventDomain,
        Unavailability $unavailability,
        Participant $participant,
        Sheet $sheet
    ) {
        $event = $eventDomain->getEvent();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::UNAVAILABILITY_REMOVE, $sheet);
        $this->denyAccessUnlessGranted(AgendaAccessVoter::PERMISSION, $event);
        $this->checkSheetHasParticipant($sheet, $participant);

        try {
            $this->get('tactician.commandbus')->handle(new Remove($unavailability));
        } catch (CanNotDeleteUnavailabilityException $exception) {
            $this->addFlash('error', 'flash.unavailability.remove.cancelAttendance.error');
        }

        return $this->redirectToRoute(
            'event_agenda_participant',
            [
                'participant' => $participant->getId(),
                'sheet' => $participant->getSheet()->getId(),
            ]
        );
    }

    /**
     * @param Sheet       $sheet
     * @param Participant $participant
     */
    private function checkSheetHasParticipant(Sheet $sheet, Participant $participant)
    {
        if (!$sheet->hasParticipant($participant)) {
            throw $this->createNotFoundException(
                sprintf(
                    'The given participant %s is not on the sheet %s',
                    $participant->getId(),
                    $sheet->getId()
                )
            );
        }
    }
}
