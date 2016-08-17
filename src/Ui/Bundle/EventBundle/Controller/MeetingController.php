<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Meeting\Cancel;
use Proximum\Vimeet\Application\Command\Meeting\Update;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\CancelType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\UpdateType;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MeetingController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Meeting     $meeting
     *
     * @return Response
     */
    public function displayAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Meeting $meeting)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $locale = $request->getLocale();

        $participantInfoGuesser = $this->get('template.participant_info_guesser');
        $sheetInfoGuesser       = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        return $this->render('EventBundle:Meeting:display.html.twig', [
            'event'            => $eventDomain->getEvent(),
            'sheet'            => $sheet,
            'meeting'          => $meeting,
            'from'             => $sheetInfoGuesser->guessSheetName($meeting->getFromSheet(), $locale),
            'to'               => $sheetInfoGuesser->guessSheetName($meeting->getToSheet(), $locale),
            'fromParticipants' => array_map(
                function (Participant $participant) use ($participantInfoGuesser, $locale) {
                    return $participantInfoGuesser->guessParticipantCompleteName($participant, $locale);
                },
                $meeting->getFromParticipants()->toArray()
            ),
            'toParticipants' => array_map(
                function (Participant $participant) use ($participantInfoGuesser, $locale) {
                    return $participantInfoGuesser->guessParticipantCompleteName($participant, $locale);
                },
                $meeting->getToParticipants()->toArray()
            ),
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Meeting     $meeting
     *
     * @return RedirectResponse|Response
     */
    public function cancelAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Meeting $meeting)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $cancel = new Cancel($meeting, $this->getUser(), $sheet, new \DateTimeImmutable());
        $form   = $this->createForm(CancelType::class, $cancel);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($cancel);
            $this->addFlash('success', 'flash.event.schedule.meeting.cancel.success');

            return $this->redirectToRoute('event_sheet_schedule', ['sheet' => $sheet->getId()]);
        }

        return $this->render('EventBundle:Meeting:cancel.html.twig', [
            'event'   => $eventDomain->getEvent(),
            'sheet'   => $sheet,
            'meeting' => $meeting,
            'form'    => $form->createView(),
        ]);
    }

    /**
     * @param Request   $request
     * @param EventDomain $eventDomain
     * @param Sheet     $sheet
     * @param Meeting   $meeting
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Meeting $meeting)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $update = new Update($meeting, $sheet, $this->getUser(), new \DateTimeImmutable());
        $form   = $this->createForm(UpdateType::class, $update, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.event.schedule.meeting.update.success');

            return $this->redirectToRoute('event_sheet_schedule', ['sheet' => $sheet->getId()]);
        }

        return $this->render('EventBundle:Meeting:update.html.twig', [
            'event'   => $eventDomain->getEvent(),
            'sheet'   => $sheet,
            'meeting' => $meeting,
            'form'    => $form->createView(),
        ]);
    }
}
