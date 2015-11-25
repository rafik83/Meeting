<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Domain\View\ScheduleSlotView;
use Proximum\Vimeet\Domain\View\ScheduleView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ScheduleController extends Controller
{
    public function eventAction(EventView $eventView)
    {
        $schedules = $this->get('vimeet_infrastructure.repository.schedule_repository')->findByEvent($eventView->id);

        $scheduleViews = [];

        foreach ($schedules as $schedule) {
            $slots = [];

            foreach ($schedule->getMeetingSlots() as $meetingSlot) {
                $slots[] = new ScheduleSlotView('Créneau de RdV', $meetingSlot->getBegin(), $meetingSlot->getEnd());
            }

            foreach ($schedule->getHappenings() as $happening) {
                $slots[] = new ScheduleSlotView($happening->getTitle(), $happening->getBegin(), $happening->getEnd());
            }

            usort($slots, function (ScheduleSlotView $one, ScheduleSlotView $another) {
                return $one->begin->getTimestamp() - $another->begin->getTimestamp();
            });

            $scheduleViews[] = new ScheduleView($schedule->getDate(), $slots);
        }

        return $this->render('VimeetAppBundle:Event/Schedule:event.html.twig', [
            'eventView'     => $eventView,
            'scheduleViews' => $scheduleViews,
        ]);
    }
}
