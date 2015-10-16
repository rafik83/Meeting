<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller;

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
}
