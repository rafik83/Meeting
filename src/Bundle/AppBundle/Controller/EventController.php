<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EventController extends Controller
{
    /**
     * @ParamConverter("event", converter="event_domain_param_converter")
     *
     * @param Event $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Event $event)
    {
        return $this->render('VimeetAppBundle:Event:index.html.twig', [
            'event' => $event,
        ]);
    }
}
