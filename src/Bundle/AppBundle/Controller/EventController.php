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

class EventController extends Controller
{
    /**
     * @param EventView $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(EventView $event)
    {
        return $this->render('VimeetAppBundle:Event:index.html.twig', [
            'event' => $event,
        ]);
    }
}
