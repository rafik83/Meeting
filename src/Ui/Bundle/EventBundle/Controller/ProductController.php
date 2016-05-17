<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * @param EventView $eventView
     *
     * @return Response
     */
    public function step1Action(EventView $eventView)
    {
        return $this->render('EventBundle:Product:step1.html.twig', [
            'eventView'     => $eventView,
        ]);
    }

    /**
     * @param EventView $eventView
     *
     * @return Response
     */
    public function step2Action(EventView $eventView)
    {
        return $this->render('EventBundle:Product:step2.html.twig', [
            'eventView'     => $eventView,
        ]);
    }

    /**
     * @param EventView $eventView
     *
     * @return Response
     */
    public function step3Action(EventView $eventView)
    {
        return $this->render('EventBundle:Product:step3.html.twig', [
            'eventView'     => $eventView,
        ]);
    }
}
