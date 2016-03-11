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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    /**
     * @return Response
     */
    public function listAction()
    {
        $events = $this
            ->get('vimeet_infrastructure.repository.event_repository')
            ->getListByAdmin($this->getUser());


        return $this->render('VimeetAppBundle:Admin/Event:list.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function readAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        return $this->render('VimeetAppBundle:Admin/Event:read.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $update = new Update($event);

        $form = $this->createForm(EventUpdateType::class, $update, [
            'locales' => $event->getLocales(),
            'method'  => 'POST',
            'action'  => $this->generateUrl('admin_event_update', ['event' => $event->getId()]),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.update_handler')->handle($update);
            $this->addFlash('success', 'flash.admin.event.update.success');

            return $this->redirectToRoute('admin_event_update', ['event' => $event->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/Event:update.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
        ]);
    }
}
