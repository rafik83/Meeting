<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Application\Command\Unavailability\AddUnavailability;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Unavailability\AddUnavailabilityType;
use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Domain\View\ScheduleSlotView;
use Proximum\Vimeet\Domain\View\ScheduleView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

        $schedules = $this->get('vimeet_infrastructure.repository.schedule_repository')->findByEvent($eventView->id);

        $scheduleViews = [];

        foreach ($schedules as $schedule) {
            $slots = [];

            $meetingSlots = $schedule->getMeetingSlots();
            foreach ($meetingSlots as $meetingSlot) {
                $slots['meetingSlots'][] = new ScheduleSlotView('Créneau de RdV', $meetingSlot->getBegin(), $meetingSlot->getEnd());
            }

            usort($slots['meetingSlots'], function (ScheduleSlotView $one, ScheduleSlotView $another) {
                return $one->begin->getTimestamp() - $another->begin->getTimestamp();
            });

            $happenings = $schedule->getHappenings();
            foreach ($happenings as $happening) {
                $slots['happening'][] = new ScheduleSlotView($happening->getTitle(), $happening->getBegin(), $happening->getEnd());
            }

            usort($slots['happening'], function (ScheduleSlotView $one, ScheduleSlotView $another) {
                return $one->begin->getTimestamp() - $another->begin->getTimestamp();
            });

            $unavailabilities = $this->get('vimeet_infrastructure.repository.unavailability_repository')->findByScheduleSheetAndUser($schedule, $sheet, $this->getUser());
            foreach ($unavailabilities as $unavailability) {
                $slots['unavailability'][] = new ScheduleSlotView('Indisponible', $unavailability->getBegin(), $unavailability->getEnd());
            }

            usort($slots['unavailability'], function (ScheduleSlotView $one, ScheduleSlotView $another) {
                return $one->begin->getTimestamp() - $another->begin->getTimestamp();
            });

            $scheduleViews[] = new ScheduleView($schedule->getId(), $schedule->getDate(), $slots);
        }

        return $this->render('VimeetAppBundle:Event/Schedule:display.html.twig', [
            'eventView'     => $eventView,
            'scheduleViews' => $scheduleViews,
            'sheet'         => $sheet,
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

        $command = new AddUnavailability($schedule);
        $form    = $this->createForm(new AddUnavailabilityType(), $command, [
            'action' => $this->generateUrl('event_sheet_schedule_add_unavailability', [
                'subdomain'   => $request->attributes->get('subdomain'),
                'id'          => $sheet->getId(),
                'schedule_id' => $schedule->getId(),
            ]),
            'method' => 'POST',
            'sheet'  => $sheet,
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.unavailability.add_unavailability_handler')->handle($command);
            $this->addFlash('success', 'flash.event.schedule.add_unavailability.success');

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
}
