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
use Proximum\Vimeet\Application\Components\Type\HasAvailabilityManagementEnabled;
use Proximum\Vimeet\Application\Components\Type\HasUnavailabilityManagementDisabled;
use Proximum\Vimeet\Application\Exception\Unavailability\CanNotDeleteUnavailabilityException;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AgendaAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\Availability\ConfirmationType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Unavailability\CreateForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Unavailability\CreateFormView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UnavailabilityController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Participant $participant
     * @param Sheet       $sheet
     * @param UserDomain  $userDomain
     *
     * @return RedirectResponse|Response
     */
    public function createAction(
        Request $request,
        EventDomain $eventDomain,
        Participant $participant,
        Sheet $sheet,
        UserDomain $userDomain
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::UNAVAILABILITY_ADD, $sheet);
        $this->denyAccessUnlessGranted(AgendaAccessVoter::PERMISSION, $eventDomain->getEvent());
        $this->checkSheetHasParticipant($sheet, $participant);
        $this->checkDisableUnavailabilityManagement($sheet);

        $event = $eventDomain->getEvent();
        $user  = $userDomain->getUser();

        $actionUrl = $this->generateUrl('event_unavailability_create', [
            'participant' => $participant->getId(),
            'sheet'       => $sheet->getId(),
        ]);

        $timezone = $this->get(GetTimezoneHelper::class)->getTimezoneByEventAndParticipant($eventDomain->getEvent(), $participant);

        /** @var CreateFormView $createFormView */
        $createFormView = $this->get('handler.unavailability.create_form_handler')->handle(
            new CreateForm($request, $event, $sheet, $user, $actionUrl, $timezone)
        );

        if ($createFormView->isXmlHttpRequest()) {
            return $this->render('EventBundle:Unavailability:create-form.html.twig', [
                'form_unavailability' => $createFormView->formView,
            ]);
        }

        if ($createFormView->isSuccess()) {
            return $this->redirectToRoute('event_agenda_participant', [
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
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_AGENDA,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);

        $timezone = $eventDomain->getEvent()->getTimeZone();
        if ($this->get(IsParticipantVisio::class)->isSatisfiedBy($participant) && $participant->getTimezone()) {
            $timezone = $participant->getTimezone();
        }

        return $this->render('EventBundle:Unavailability:create.html.twig', [
            'event' => $event,
            'participant' => $participant,
            'agenda' => $agenda,
            'sheet' => $sheet,
            'form_unavailability' => $createFormView->formView,
            'tipTranslationViews' => $tipTranslationViews,
            'timezone' => $timezone,
            'isVisio' => $this->get(IsParticipantVisio::class)->isSatisfiedBy($participant),
            'isUnavailabilityManagementDisabled' => $this->get(HasUnavailabilityManagementDisabled::class)->isSatisfiedBy($sheet),
            'isAvailabilityManagementEnabled' => $this->get(HasAvailabilityManagementEnabled::class)->isSatisfiedBy($sheet),
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param UserDomain  $userDomain
     *
     * @return Response
     */
    public function createFromConfirmationAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        UserDomain $userDomain
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::UNAVAILABILITY_ADD, $sheet);
        $this->denyAccessUnlessGranted(AgendaAccessVoter::PERMISSION, $eventDomain->getEvent());

        $event = $eventDomain->getEvent();

        $actionUrl = $this->generateUrl('event_unavailability_create_from_confirmation', [
            'sheet' => $sheet->getId(),
        ]);

        $participant = $sheet->getUserParticipant($userDomain->getUser());
        $timezone = $participant instanceof Participant
            ? $this->get(GetTimezoneHelper::class)->getTimezoneByEventAndParticipant($eventDomain->getEvent(), $participant)
            : $event->getTimeZone()
        ;

        /** @var CreateFormView $createFormView */
        $createFormView = $this->get('handler.unavailability.create_form_handler')->handle(
            new CreateForm(
                $request,
                $event,
                $sheet,
                $userDomain->getUser(),
                $actionUrl,
                $timezone
            )
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

        $availabilityConfirmation = new Confirmation($eventDomain->getEvent(), $userDomain->getUser());
        $form = $this->createForm(ConfirmationType::class, $availabilityConfirmation, [
            'action' => $this->generateUrl('event_availability_confirmation', [
                'sheet' => $sheet->getId(),
            ]),
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
    ): RedirectResponse {
        $event = $eventDomain->getEvent();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::UNAVAILABILITY_REMOVE, $sheet);
        $this->denyAccessUnlessGranted(AgendaAccessVoter::PERMISSION, $event);
        $this->checkSheetHasParticipant($sheet, $participant);
        $this->checkDisableUnavailabilityManagement($sheet);

        try {
            $this->get('tactician.commandbus')->handle(new Remove($unavailability));
        } catch (CanNotDeleteUnavailabilityException $exception) {
            $this->addFlash('error', 'flash.unavailability.remove.error');
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
    private function checkSheetHasParticipant(Sheet $sheet, Participant $participant): void
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

    private function checkDisableUnavailabilityManagement(Sheet $sheet): void
    {
        if ($this->get(HasUnavailabilityManagementDisabled::class)->isSatisfiedBy($sheet)) {
            throw new AccessDeniedException();
        }
    }
}
