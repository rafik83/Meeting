<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class WaitingController extends Controller
{
    public function indexAction(Request $request, EventDomain $eventDomain)
    {
        $event = $eventDomain->getEvent();

        return $this->render('EventBundle:WaitingPage:index.html.twig', [
            'event' => $event,
        ]);
    }
}
