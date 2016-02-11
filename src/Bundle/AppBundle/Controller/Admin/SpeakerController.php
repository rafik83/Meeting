<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Happening\Speaker\Create;
use Proximum\Vimeet\Application\Command\Happening\Speaker\Delete;
use Proximum\Vimeet\Application\Command\Happening\Speaker\Update;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Happening\Speaker\CreateSpeakerType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Happening\Speaker\UpdateSpeakerType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SpeakerController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function listAction(Event $event)
    {
        $speakers = $this->get('repository.happening.speaker')->allByEvent($event);

        return $this->render('VimeetAppBundle:Admin/Speaker:list.html.twig', [
            'event'    => $event,
            'speakers' => $speakers,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, Event $event)
    {
        $command = new Create($event);
        $form    = $this->createForm(CreateSpeakerType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.happening.speaker.create_handler')->handle($command);
            $this->addFlash('success', 'flash.admin.speaker.create.success');

            return $this->redirectToRoute('admin_happening_speaker_list', ['event' => $event->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/Speaker:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Speaker $speaker
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event, Speaker $speaker)
    {
        $command = new Update($speaker);
        $form    = $this->createForm(UpdateSpeakerType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.happening.speaker.update_handler')->handle($command);
            $this->addFlash('success', 'flash.admin.speaker.update.success');

            return $this->redirectToRoute('admin_happening_speaker_update', ['event' => $event->getId(), 'speaker' => $speaker->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/Speaker:update.html.twig', [
            'event'   => $event,
            'speaker' => $speaker,
            'form'    => $form->createView(),
        ]);
    }

    /**
     * @param Event   $event
     * @param Speaker $speaker
     *
     * @return Response
     */
    public function readAction(Request $request, Event $event, Speaker $speaker)
    {
        $happenings = $this
            ->get('happening.happening_list_view_factory')
            ->getListBySpeakerAndLocale($speaker, $request->getLocale());

        return $this->render('VimeetAppBundle:Admin/Speaker:read.html.twig', [
            'event'      => $event,
            'speaker'    => $speaker,
            'happenings' => $happenings,
        ]);
    }

    /**
     * @param Event   $event
     * @param Speaker $speaker
     *
     * @return RedirectResponse
     */
    public function deleteAction(Event $event, Speaker $speaker)
    {
        $this->get('command.happening.speaker.delete_handler')->handle(new Delete($speaker));
        $this->addFlash('success', 'flash.admin.speaker.delete.success');

        return $this->redirectToRoute('admin_happening_speaker_list', ['event' => $event->getId()]);
    }
}
