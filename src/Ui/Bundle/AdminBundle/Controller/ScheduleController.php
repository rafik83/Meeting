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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ScheduleController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function slotsAction(Event $event)
    {
        return $this->render('AdminBundle:Schedule:slots.html.twig', [
            'event' => $event,
        ]);
    }
}
