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
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Domain\View\ScheduleSlotView;
use Proximum\Vimeet\Domain\View\ScheduleView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ScheduleController extends Controller
{
    /**
     * @param EventView $eventView
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayAction(EventView $eventView)
    {
        $schedules = $this->get('vimeet_infrastructure.repository.schedule_repository')->findByEvent($eventView->id);

        $scheduleViews = [];

        foreach ($schedules as $schedule) {
            $slots = [];

            foreach ($schedule->getMeetingSlots() as $meetingSlot) {
                $slots[] = new ScheduleSlotView('Pas de RdV', $meetingSlot->getBegin(), $meetingSlot->getEnd());
            }

            foreach ($schedule->getHappenings() as $happening) {
                $slots[] = new ScheduleSlotView($happening->getTitle(), $happening->getBegin(), $happening->getEnd());
            }

            usort($slots, function (ScheduleSlotView $one, ScheduleSlotView $another) {
                return $one->begin->getTimestamp() - $another->begin->getTimestamp();
            });

            $scheduleViews[] = new ScheduleView($schedule->getDate(), $slots);
        }

        return $this->render('VimeetAppBundle:Event/Schedule:display.html.twig', [
            'eventView'     => $eventView,
            'scheduleViews' => $scheduleViews,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addUnavailabilityAction(Request $request, EventView $eventView)
    {
        $command = new AddUnavailability();
        $form    = $this->createForm(new AddUnavailabilityType(), $command, [
            'action' => $this->generateUrl('event_schedule_add_unavailability', ['subdomain' => $request->attributes->get('subdomain')]),
            'method' => 'POST',
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.unavailability.add_unavailability_handler')->handle($command);
            $this->addFlash('success', 'flash.event.schedule.add_unavailability.success');

            return $this->redirectToRoute('event_schedule', [
                'subdomain' => $request->attributes->get('subdomain'),
            ]);
        }

        return $this->render('VimeetAppBundle:Event/Schedule:addUnavailability.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }
}
