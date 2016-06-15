<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Schedule\ConfigType;
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
     * @param Event $event
     *
     * @return Response
     */
    public function slotsAction(Event $event)
    {
        $form = $this->createForm(ConfigType::class, [], ['submit' => true]);

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
        $command = new Generate($event);
        $form    = $this->createForm(GenerateType::class, $command, ['submit' => true]);

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
}
