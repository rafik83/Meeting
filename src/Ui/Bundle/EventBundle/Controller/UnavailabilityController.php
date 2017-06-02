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
use Proximum\Vimeet\Application\Exception\Unavailability\CanNotDeleteUnavailabilityException;
use Proximum\Vimeet\Application\Exception\Unavailability\NoParticipantSelectedException;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsSelectedWithMeetingOrHappeningException;
use Proximum\Vimeet\Application\Exception\Unavailability\TimeOutOfRangeException;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Event\Day\DayHelper;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability\CreateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
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
     * @param Sheet       $sheet
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, EventDomain $eventDomain, Participant $participant, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::UNAVAILABILITY_ADD, $sheet);
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());
        $this->checkSheetHasParticipant($sheet, $participant);

        $event = $eventDomain->getEvent();
        $user  = $this->getUser();

        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($user, $sheet);

        $create = new Create($event, $sheet, $user, $request->getLocale());
        $form   = $this->createForm(CreateType::class, $create, [
            'action'                 => $this->generateUrl(
                'event_unavailability_create',
                [
                    'participant' => $participant->getId(),
                    'sheet'       => $participant->getSheet()->getId(),
                ]
            ),
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

                return $this->redirectToRoute(
                    'event_agenda_participant',
                    [
                        'participant' => $participant->getId(),
                        'sheet' => $participant->getSheet()->getId(),
                    ]
                );
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
                new AgendaViewQuery($event, $sheet, $participant, $request->getLocale(), $user)
            );

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            TipTranslationViewQueryHandler::CONTEXT_AGENDA,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);

        return $this->render('EventBundle:Unavailability:create.html.twig', [
            'event'               => $event,
            'agenda'              => $agenda,
            'sheet'               => $sheet,
            'form_unavailability' => $form->createView(),
            'tipTranslationViews' => $tipTranslationViews,
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
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $event);
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
