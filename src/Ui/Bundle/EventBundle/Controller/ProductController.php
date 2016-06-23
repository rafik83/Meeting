<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function step1Action(EventDomain $eventDomain)
    {
        return $this->render('EventBundle:Product:step1.html.twig', [
            'event' => $eventDomain->getEvent(),
        ]);
    }

    /**
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function step2Action(EventDomain $eventDomain)
    {
        return $this->render('EventBundle:Product:step2.html.twig', [
            'event' => $eventDomain->getEvent(),
        ]);
    }

    /**
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function step3Action(EventDomain $eventDomain)
    {
        return $this->render('EventBundle:Product:step3.html.twig', [
            'event' => $eventDomain->getEvent(),
        ]);
    }
}
