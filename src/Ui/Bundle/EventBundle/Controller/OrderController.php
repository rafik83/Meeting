<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function listOrderAction(EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('EventBundle:Order:listOrders.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
        ]);
    }
}
