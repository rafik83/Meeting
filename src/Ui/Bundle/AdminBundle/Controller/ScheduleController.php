<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Event\Day\Update;
use Proximum\Vimeet\Application\Command\MeetingSlot\Lock;
use Proximum\Vimeet\Application\Command\MeetingSlot\Unlock;
use Proximum\Vimeet\Application\Command\Schedule\Configure;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Day\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Schedule\ConfigureType;
use Proximum\Vimeet\Ui\Flash\TranschoiceMessage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Schedule\GenerateType;
use Proximum\Vimeet\Application\Command\MeetingSlot\Generate;

class ScheduleController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function slotsAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $command = new Configure($event);
        $form    = $this->createForm(ConfigureType::class, $command, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('success', 'flash.schedule.configure.success');

            return $this->redirectToRoute('admin_schedule_slots', ['event' => $event->getId()]);
        }

        $slots = $this->get('vimeet_infrastructure.repository.meeting_slot_repository')->findByEvent($event);

        return $this->render('AdminBundle:Schedule:slots.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
            'slots' => $slots,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function generateAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $command = new Generate($event);
        $form    = $this->createForm(GenerateType::class, $command, ['submit' => true, 'event' => $event]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('success', new TranschoiceMessage('flash.schedule.slot.generate.success', $result->count, [
                '%count%' => $result->count,
            ]));

            return $this->redirectToRoute('admin_schedule_slots', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Schedule:generate.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Event       $event
     * @param MeetingSlot $meetingSlot
     *
     * @return RedirectResponse
     */
    public function lockAction(Event $event, MeetingSlot $meetingSlot)
    {
        return $this->handleAndRedirect($event, $meetingSlot, new Lock($meetingSlot));
    }

    /**
     * @param Event       $event
     * @param MeetingSlot $meetingSlot
     *
     * @return RedirectResponse
     */
    public function unlockAction(Event $event, MeetingSlot $meetingSlot)
    {
        return $this->handleAndRedirect($event, $meetingSlot, new Unlock($meetingSlot));
    }

    /**
     * @param Event       $event
     * @param MeetingSlot $meetingSlot
     */
    private function denyAccessIfWrongEvent(Event $event, MeetingSlot $meetingSlot)
    {
        if ($meetingSlot->getEvent() !== $event) {
            throw $this->createAccessDeniedException('This meeting slot is not available for this event.');
        }
    }

    /**
     * @param Event       $event
     * @param MeetingSlot $meetingSlot
     * @param mixed       $command
     *
     * @return RedirectResponse
     */
    private function handleAndRedirect(Event $event, MeetingSlot $meetingSlot, $command)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessIfWrongEvent($event, $meetingSlot);

        $this->get('tactician.commandbus')->handle($command);

        return $this->redirectToRoute('admin_schedule_slots', ['event' => $meetingSlot->getEvent()->getId()]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function dayAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $update = new Update($event);
        $form   = $this->createForm(UpdateType::class, $update, [
            'event'  => $event,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.schedule.days.success');

            return $this->redirectToRoute('admin_schedule_slots', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:Schedule:day.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
