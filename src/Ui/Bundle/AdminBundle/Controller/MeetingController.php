<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MeetingController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $meetings = $this
            ->get('vimeet_infrastructure.repository.meeting_repository')
            ->getByEvent($event, $request->query->getInt('page', 1), 20);

        return $this->render('VimeetAppBundle:Admin/Meeting:list.html.twig', [
            'event'    => $event,
            'meetings' => $meetings,
        ]);
    }
}
