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
            $meetingSlots = [];

            foreach ($schedule->getMeetingSlots() as $meetingSlot) {
                $meetingSlots[] = new ScheduleSlotView('RdV', $meetingSlot->getBegin(), $meetingSlot->getEnd());
            }

            foreach ($schedule->getHappenings() as $happening) {
                $meetingSlots[] = new ScheduleSlotView($happening->getTitle(), $happening->getBegin(), $happening->getEnd());
            }

            $scheduleViews[] = new ScheduleView($schedule->getDate(), $meetingSlots);
        }

        return $this->render('VimeetAppBundle:Event/Schedule:event.html.twig', [
            'eventView'     => $eventView,
            'scheduleViews' => $scheduleViews,
        ]);
    }
}
