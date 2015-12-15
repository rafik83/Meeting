<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Application\Command\Happening\Unparticipate;
use Proximum\Vimeet\Application\Command\Unavailability\Add;
use Proximum\Vimeet\Application\Command\Unavailability\Remove;
use Proximum\Vimeet\Application\Command\Unavailability\Update;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Happening\ParticipateHappeningType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Unavailability\AddUnavailabilityType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Unavailability\UpdateUnavailabilityType;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\View\EventView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScheduleController extends Controller
{
    /**
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function displayAction(EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $participantSchedules = $this
            ->get('proximum.vimeet.application.components.schedule.schedule_builder')
            ->buildForSheet($sheet);

        return $this->render('VimeetAppBundle:Event/Schedule:display.html.twig', [
            'eventView'            => $eventView,
            'participantSchedules' => $participantSchedules,
            'sheet'                => $sheet,
        ]);
    }

    /**
     * @ParamConverter(
     *   "schedule",
     *   class="Proximum\Vimeet\Domain\Model\Schedule",
     *   options={"id" = "schedule_id"}
     * )
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param Schedule  $schedule
     *
     * @return RedirectResponse|Response
     */
    public function addUnavailabilityAction(Request $request, EventView $eventView, Sheet $sheet, Schedule $schedule)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $command = new Add($schedule);
        $form    = $this->createForm(AddUnavailabilityType::class, $command, [
            'action' => $this->generateUrl('event_sheet_schedule_add_unavailability', [
                'subdomain'   => $request->attributes->get('subdomain'),
                'id'          => $sheet->getId(),
                'schedule_id' => $schedule->getId(),
            ]),
            'method' => 'POST',
            'sheet'  => $sheet,
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.unavailability.add_unavailability_handler')->handle($command);
            $this->addFlash('success', 'flash.event.schedule.unavailability.add.success');

            return $this->redirectToRoute('event_sheet_schedule', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Event/Schedule:addUnavailability.html.twig', [
            'eventView' => $eventView,
            'schedule'  => $schedule,
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
     *   "unavailability",
     *   class="Proximum\Vimeet\Domain\Model\Unavailability",
     *   options={"id" = "unavailability_id"}
     * )
     *
     * @param Request        $request
     * @param EventView      $eventView
     * @param Sheet          $sheet
     * @param Schedule       $schedule
     * @param Unavailability $unavailability
     *
     * @return RedirectResponse|Response
     */
    public function updateUnavailabilityAction(Request $request, EventView $eventView, Sheet $sheet, Schedule $schedule, Unavailability $unavailability)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($unavailability->getSchedule() !== $schedule) {
            $this->createNotFoundException('Unavailability not found.');
        }

        $command = new Update($unavailability);
        $form    = $this->createForm(UpdateUnavailabilityType::class, $command, [
            'action' => $this->generateUrl('event_sheet_schedule_update_unavailability', [
                'subdomain'         => $request->attributes->get('subdomain'),
                'id'                => $sheet->getId(),
                'schedule_id'       => $schedule->getId(),
                'unavailability_id' => $unavailability->getId(),
            ]),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.unavailability.update_handler')->handle($command);
            $this->addFlash('success', 'flash.event.schedule.unavailability.update.success');

            return $this->redirectToRoute('event_sheet_schedule', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Event/Schedule:updateUnavailability.html.twig', [
            'eventView' => $eventView,
            'schedule'  => $schedule,
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
     *   "unavailability",
     *   class="Proximum\Vimeet\Domain\Model\Unavailability",
     *   options={"id" = "unavailability_id"}
     * )
     *
     * @param Request        $request
     * @param EventView      $eventView
     * @param Sheet          $sheet
     * @param Schedule       $schedule
     * @param Unavailability $unavailability
     *
     * @return RedirectResponse|Response
     */
    public function removeUnavailabilityAction(Request $request, EventView $eventView, Sheet $sheet, Schedule $schedule, Unavailability $unavailability)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($unavailability->getSchedule() !== $schedule) {
            $this->createNotFoundException('Unavailability not found.');
        }

        $command = new Remove($unavailability);
        $this->get('vimeet_infrastructure.vimeet.application.command.unavailability.remove_handler')->handle($command);
        $this->addFlash('success', 'flash.event.schedule.unavailability.remove.success');

        return $this->redirectToRoute('event_sheet_schedule', [
            'subdomain' => $request->attributes->get('subdomain'),
            'id'        => $sheet->getId(),
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
     *   "happening",
     *   class="Proximum\Vimeet\Domain\Model\Happening",
     *   options={"id" = "happening_id"}
     * )
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param Schedule  $schedule
     * @param Happening $happening
     *
     * @return RedirectResponse|Response
     */
    public function participateHappeningAction(Request $request, EventView $eventView, Sheet $sheet, Schedule $schedule, Happening $happening)
    {
        // Get user participant
        $participant = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getParticipantForUserAndSheet($this->getUser(), $sheet);

        // Command and form
        $command = new Participate($happening, [$participant]);
        $form    = $this->createForm(ParticipateHappeningType::class, $command, [
            'sheet'     => $sheet,
            'happening' => $happening,
        ]);
        $form->add('submit', SubmitType::class);

        // Handle form
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.components.happening.participate_handler')
                ->handle($command);

            $this->addFlash('success', 'flash.event.schedule.happening.participate.success');

            return $this->redirectToRoute('event_sheet_schedule', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Event/Schedule:participateHappening.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'schedule'  => $schedule,
            'happening' => $happening,
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
     *   "happening",
     *   class="Proximum\Vimeet\Domain\Model\Happening",
     *   options={"id" = "happening_id"}
     * )
     *
     * @ParamConverter(
     *   "participant",
     *   class="Proximum\Vimeet\Domain\Model\Participant",
     *   options={"id" = "participant_id"}
     * )
     *
     * @param Request     $request
     * @param EventView   $eventView
     * @param Sheet       $sheet
     * @param Schedule    $schedule
     * @param Happening   $happening
     * @param Participant $participant
     *
     * @return RedirectResponse
     */
    public function unparticipateHappeningAction(Request $request, EventView $eventView, Sheet $sheet, Schedule $schedule, Happening $happening, Participant $participant)
    {
        // Unparticipate
        $this
            ->get('vimeet_infrastructure.vimeet.application.components.happening.unparticipate_handler')
            ->handle(new Unparticipate($happening, $participant));

        $this->addFlash('success', 'flash.event.schedule.happening.unparticipate.success');

        return $this->redirectToRoute('event_sheet_schedule', [
            'subdomain' => $request->attributes->get('subdomain'),
            'id'        => $sheet->getId(),
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
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param Schedule  $schedule
     * @param Meeting   $meeting
     *
     * @return Response
     */
    public function meetingAction(EventView $eventView, Sheet $sheet, Schedule $schedule, Meeting $meeting)
    {
        $participantInfoGuesser = $this->get('vimeet_infrastructure.application.components.sheet.participant_info_guesser');
        $sheetInfoGuesser       = $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser');

        return $this->render('VimeetAppBundle:Event/Schedule:meeting.html.twig', [
            'eventView'        => $eventView,
            'sheet'            => $sheet,
            'schedule'         => $schedule,
            'meeting'          => $meeting,
            'from'             => $sheetInfoGuesser->guessSheetInfo($meeting->getFromSheet()),
            'to'               => $sheetInfoGuesser->guessSheetInfo($meeting->getToSheet()),
            'fromParticipants' => array_map(function (Participant $participant) use ($participantInfoGuesser) {
                return $participantInfoGuesser->guessParticipantInfo($participant);
            }, $meeting->getFromParticipants()->toArray()),
            'toParticipants'   => array_map(function (Participant $participant) use ($participantInfoGuesser) {
                return $participantInfoGuesser->guessParticipantInfo($participant);
            }, $meeting->getToParticipants()->toArray()),
        ]
        );
    }
}
