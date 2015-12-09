<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Event\Update;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event\EventUpdateType;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class EventController extends Controller
{
    public function listAction(Request $request)
    {
        $events = $this
            ->get('vimeet_infrastructure.repository.event_repository')
            ->paginate($request->query->get('page', 1), 10);

        return $this->render('VimeetAppBundle:Admin/Event:list.html.twig', [
            'events' => $events,
        ]);
    }

    public function updateAction(Request $request, Event $event)
    {
        $update = new Update($event);

        $form = $this->createForm(EventUpdateType::class, $update, [
            'locales' => $event->getLocales(),
            'method'  => 'POST',
            'action'  => $this->generateUrl('admin_event_update', ['id' => $event->getId()]),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.update_handler')->handle($update);
            $this->addFlash('success', 'flash.admin.event.update.success');

            return $this->redirectToRoute('admin_event_update', ['id' => $event->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/Event:update.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
        ]);
    }
}
