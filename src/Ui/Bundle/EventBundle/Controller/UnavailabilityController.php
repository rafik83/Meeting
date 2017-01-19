<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Unavailability\Create;
use Proximum\Vimeet\Application\Command\Unavailability\Remove;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Exception\Unavailability\NoParticipantSelectedException;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsSelectedWithMeetingOrHappeningException;
use Proximum\Vimeet\Application\Exception\Unavailability\TimeOutOfRangeException;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Event\Day\DayHelper;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability\CreateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UnavailabilityController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Participant $participant
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function createAction(Request $request, EventDomain $eventDomain, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        $event = $eventDomain->getEvent();
        $user  = $this->getUser();

        try {
            $sheet = $this
                ->get('sheet.sheet_guesser')
                ->getUserSheet($this->getUser(), $event, $request->getLocale());
        } catch (SheetNotFoundException $exception) {
            throw $this->createNotFoundException('Sheet not found');
        }

        if (!$sheet->hasParticipant($participant)) {
            throw $this->createNotFoundException(sprintf(
                'The given participant %s is not on the sheet %s',
                $participant->getId(),
                $sheet->getId()
            ));
        }

        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($user, $sheet);

        $create = new Create($event, $sheet, $user, $request->getLocale());
        $form   = $this->createForm(CreateType::class, $create, [
            'action'                 => $this->generateUrl('event_unavailability_create', ['participant' => $participant->getId()]),
            'isUserAloneParticipant' => $isUserAloneParticipant,
            'event'                  => $event,
            'locale'                 => $request->getLocale(),
            'sheet'                  => $sheet,
        ]);

        // If the page is called by an ajax request, only show the form
        if ($request->isXmlHttpRequest()) {
            return $this->render('EventBundle:Unavailability:create-form.html.twig', [
                'form_unavailability' => $form->createView(),
            ]);
        }

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($create);

                return $this->redirectToRoute('event_agenda_participant', ['participant' => $participant->getId()]);
            } catch (NoParticipantSelectedException $exception) {
                if ($form->has('participants')) {
                    $form->get('participants')->addError($this->createNoParticipantSelectedExceptionError());
                } else {
                    $form->addError($this->createNoParticipantSelectedExceptionError());
                }
            } catch (ParticipantsSelectedWithMeetingOrHappeningException $exception) {
                if ($form->has('participants')) {
                    $form->get('participants')->addError(
                        $this->createParticipantsSelectedWithMeetingOrHappeningExceptionError($exception)
                    );
                } else {
                    $form->addError($this->createParticipantsSelectedWithMeetingOrHappeningExceptionError($exception));
                }
            } catch (TimeOutOfRangeException $exception) {
                if ($exception->isOutOfRangeAtBeginOfDay()) {
                    $form->get('time')->get('begin')->addError(
                        new FormError(
                            $this->get('translator')->trans(
                                'validators.unavailability.timeOutOfRange.begin',
                                [
                                    '%day%' => DayHelper::getFormatter($request->getLocale(), $event->getTimeZone())
                                        ->format($exception->day->getDay()),
                                ],
                                'validators'
                            )
                        )
                    );
                } else {
                    $form->get('time')->get('end')->addError(
                        new FormError(
                            $this->get('translator')->trans(
                                'validators.unavailability.timeOutOfRange.end',
                                [
                                    '%day%' => DayHelper::getFormatter($request->getLocale(), $event->getTimeZone())
                                        ->format($exception->day->getDay())
                                ],
                                'validators'
                            )
                        )
                    );
                }
            }
        }

        /** @var AgendaView $agenda */
        $agenda = $this
            ->get('tactician.commandbus.query')
            ->handle(
                new AgendaViewQuery($event, $sheet, $participant, $user, $request->getLocale())
            );

        return $this->render('EventBundle:Unavailability:create.html.twig', [
            'event'               => $event,
            'agenda'              => $agenda,
            'form_unavailability' => $form->createView()
        ]);
    }

    /**
     * @param Request        $request
     * @param EventDomain    $eventDomain
     * @param Unavailability $unavailability
     * @param Participant    $participant
     *
     * @return RedirectResponse
     */
    public function removeAction(
        Request $request,
        EventDomain $eventDomain,
        Unavailability $unavailability,
        Participant $participant
    ) {
        $event = $eventDomain->getEvent();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $event);

        try {
            $sheet = $this
                ->get('sheet.sheet_guesser')
                ->getUserSheet($this->getUser(), $event, $request->getLocale());
        } catch (SheetNotFoundException $exception) {
            throw $this->createNotFoundException('Sheet not found');
        }

        if ($sheet != $unavailability->getParticipant()->getSheet()) {
            throw $this->createAccessDeniedException('This user can not remove this unavailability');
        }
        if (!$sheet->hasParticipant($participant)) {
            throw $this->createNotFoundException('The participant given is not on the sheet');
        }

        $this->get('tactician.commandbus')->handle(new Remove($unavailability));

        return $this->redirectToRoute('event_agenda_participant', ['participant' => $participant->getId()]);
    }

    /**
     * @param ParticipantsSelectedWithMeetingOrHappeningException $exception
     *
     * @return FormError
     */
    private function createParticipantsSelectedWithMeetingOrHappeningExceptionError(
        ParticipantsSelectedWithMeetingOrHappeningException $exception
    ) {
        return new FormError(
            $this->get('translator')->transChoice(
                'validators.unavailability.participantsWithConflict',
                $exception->getNumberOfConflict(),
                ['%participants%' => $exception->getListOfParticipantsName()],
                'validators'
            )
        );
    }

    /**
     * @return FormError
     */
    private function createNoParticipantSelectedExceptionError()
    {
        return new FormError(
            $this->get('translator')->trans('validators.unavailability.participantsNotSelected', [], 'validators')
        );
    }
}
