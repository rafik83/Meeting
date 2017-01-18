<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AgendaController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     * @param Request     $request
     *
     * @return Response|RedirectResponse
     */
    public function indexAction(EventDomain $eventDomain, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        try {
            $sheet = $this->get('sheet.sheet_guesser')->getUserSheet(
                $this->getUser(),
                $eventDomain->getEvent(),
                $request->getLocale()
            );

            $participant = $sheet->getUserParticipant($this->getUser());

            if (null !== $participant) {
                return $this->redirectToRoute(
                    'event_agenda_participant',
                    ['participant' => $participant->getId()]
                );
            }

            if ($sheet->isOwner($this->getUser())) {
                return $this->redirectToRoute(
                    'event_agenda_participant',
                    ['participant' => $sheet->getParticipants()->first()->getId()]
                );
            }
        } catch (SheetNotFoundException $exception) {
            throw $this->createNotFoundException('Sheet not found');
        }

        throw $this->createNotFoundException(sprintf(
            'User %s has a sheet %s but is not participant and owner',
            $this->getUser()->getId(),
            $sheet->getId()
        ));
    }

    /**
     * @param EventDomain $eventDomain
     * @param Request     $request
     * @param Participant $participant
     *
     * @return Response
     */
    public function participantAction(EventDomain $eventDomain, Request $request, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        $sheet = $participant->getSheet();

        if ($sheet->getEvent() !== $eventDomain->getEvent()) {
            throw $this->createNotFoundException('The participant of this sheet is not on this event');
        }

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createNotFoundException(sprintf(
                'The user %s is not participant/owner of the sheet %s',
                $this->getUser()->getId(),
                $sheet->getId()
            ));
        }

        /** @var AgendaView $agenda */
        $agenda = $this->get('tactician.commandbus.query')->handle(new AgendaViewQuery(
            $eventDomain->getEvent(),
            $sheet,
            $participant,
            $this->getUser(),
            $request->getLocale()
        ));

        return $this->render('EventBundle:Agenda:index.html.twig', [
            'event'  => $eventDomain->getEvent(),
            'agenda' => $agenda,
        ]);
    }
}
