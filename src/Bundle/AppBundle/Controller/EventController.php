<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller;

use Proximum\Vimeet\Bundle\AppBundle\Form\Type\RegisterType;
use Proximum\Vimeet\Domain\Model\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EventController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, EventView $event)
    {
        $participantTypes = $this
            ->get('vimeet_infrastructure.repository.participant.type_repository')
            ->getTypeViewByEvent($event->id, $request->getLocale());

        return $this->render('VimeetAppBundle:Event:index.html.twig', [
            'event'             => $event,
            'participant_types' => $participantTypes,
        ]);
    }

    public function registerAction(Request $request, EventView $event, $typeId)
    {
        $form = $this->createForm(new RegisterType(), null, [
            'action' => $this->generateUrl('event_register', [
                'typeId'    => $typeId,
                'subdomain' => $request->attributes->get('subdomain'),
            ]),
            'method' => 'POST',
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted()) {

            $this->addFlash('success', 'flash.event.register.success');

            return $this->redirectToRoute('event_register', [
                'typeId'    => $typeId,
                'subdomain' => $request->attributes->get('subdomain'),
            ]);
        }

        return $this->render('VimeetAppBundle:Event:register.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
        ]);
    }
}
