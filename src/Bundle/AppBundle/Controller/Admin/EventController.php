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
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event\UpdateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event\WhoSeeWhoType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\See;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $form = $this->createForm(new UpdateType(), $update, [
            'locales' => $event->getLocales(),
            'method'  => 'POST',
            'action'  => $this->generateUrl('admin_event_update', ['id' => $event->getId()]),
        ]);
        $form->add('submit', 'submit');

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

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function whoSeeWhoAction(Request $request, Event $event)
    {
        $form = $this->createForm(new WhoSeeWhoType(), [], [
            'event' => $event,
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $see = new See($event, $form->get('seer')->getData(), $form->get('seeable')->getData());
            $this->get('vimeet_infrastructure.repository.see_repository')->add($see);

            return $this->redirectToRoute('admin_event_who_see_who', ['id' => $event->getId()]);
        }

        $sees = $this->get('vimeet_infrastructure.repository.see_repository')->getByEvent($event);

        return $this->render('VimeetAppBundle:Admin/Event:whoSeeWho.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
            'sees'  => $sees,
        ]);
    }

    /**
     * @ParamConverter(
     *   "see",
     *   class="Proximum\Vimeet\Domain\Model\See",
     *   options={"id" = "see_id"}
     * )
     *
     * @param Event $event
     * @param See   $see
     *
     * @return RedirectResponse
     */
    public function deleteSeeAction(Event $event, See $see)
    {
        if ($see->getEvent() !== $event) {
            throw $this->createNotFoundException('See not found');
        }

        $this->get('vimeet_infrastructure.repository.see_repository')->remove($see);

        return $this->redirectToRoute('admin_event_who_see_who', ['id' => $event->getId()]);
    }
}
