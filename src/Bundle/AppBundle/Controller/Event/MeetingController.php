<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Application\Command\Meeting\Cancel;
use Proximum\Vimeet\Application\Command\Meeting\Update;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\CancelType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting\UpdateType;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class MeetingController extends Controller
{
    /**
     * @ParamConverter(
     *   "schedule",
     *   class="Proximum\Vimeet\Domain\Model\Schedule",
     *   options={"id" = "schedule_id"}
     * )
     *
     * @ParamConverter(
     *   "meeting",
     *   class="Proximum\Vimeet\Domain\Model\Meeting",
     *   options={"id" = "meeting_id"}
     * )
     *
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param Schedule  $schedule
     * @param Meeting   $meeting
     *
     * @return Response
     */
    public function displayAction(EventView $eventView, Sheet $sheet, Schedule $schedule, Meeting $meeting)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $participantInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.participant_info_guesser');
        $sheetInfoGuesser       = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        return $this->render('VimeetAppBundle:Event/Meeting:display.html.twig', [
            'eventView'        => $eventView,
            'sheet'            => $sheet,
            'schedule'         => $schedule,
            'meeting'          => $meeting,
            'from'             => $sheetInfoGuesser->guessSheetInfo($meeting->getFrom()),
            'to'               => $sheetInfoGuesser->guessSheetInfo($meeting->getTo()),
            'fromParticipants' => array_map(function (Participant $participant) use ($participantInfoGuesser) {
                return $participantInfoGuesser->guessParticipantInfo($participant);
            }, $meeting->getFromParticipants()->toArray()),
            'toParticipants'   => array_map(function (Participant $participant) use ($participantInfoGuesser) {
                return $participantInfoGuesser->guessParticipantInfo($participant);
            }, $meeting->getToParticipants()->toArray()),
        ]);
    }

    /**
     * @ParamConverter(
     *   "schedule",
     *   class="Proximum\Vimeet\Domain\Model\Schedule",
     *   options={"id" = "schedule_id"}
     * )
     *
     * @ParamConverter(
     *   "meeting",
     *   class="Proximum\Vimeet\Domain\Model\Meeting",
     *   options={"id" = "meeting_id"}
     * )
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param Schedule  $schedule
     * @param Meeting   $meeting
     *
     * @return RedirectResponse|Response
     */
    public function cancelAction(Request $request, EventView $eventView, Sheet $sheet, Schedule $schedule, Meeting $meeting)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $cancel = new Cancel($meeting);
        $form   = $this->createForm(new CancelType(), $cancel);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.meeting.cancel_handler')->handle($cancel);
            $this->addFlash('success', 'flash.event.schedule.meeting.cancel.success');

            return $this->redirectToRoute('event_sheet_schedule', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Event/Meeting:cancel.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'schedule'  => $schedule,
            'meeting'   => $meeting,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @ParamConverter(
     *   "schedule",
     *   class="Proximum\Vimeet\Domain\Model\Schedule",
     *   options={"id" = "schedule_id"}
     * )
     *
     * @ParamConverter(
     *   "meeting",
     *   class="Proximum\Vimeet\Domain\Model\Meeting",
     *   options={"id" = "meeting_id"}
     * )
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param Schedule  $schedule
     * @param Meeting   $meeting
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, EventView $eventView, Sheet $sheet, Schedule $schedule, Meeting $meeting)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $update = new Update($meeting, $sheet);
        $form   = $this->createForm(new UpdateType(), $update, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.meeting.update_handler')->handle($update);
            $this->addFlash('success', 'flash.event.schedule.meeting.update.success');

            return $this->redirectToRoute('event_sheet_schedule', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Event/Meeting:update.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'schedule'  => $schedule,
            'meeting'   => $meeting,
            'form'      => $form->createView(),
        ]);
    }
}
