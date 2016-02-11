<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends BaseController
{
    /**
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function listOrderAction(EventView $eventView, Sheet $sheet)
    {
        return $this->render('VimeetAppBundle:Event/Order:listOrders.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function summaryAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $summary = $this
            ->get('components.sheet.order_merge_factory')
            ->createFromSheet($sheet, $request->getLocale());

        return $this->render('VimeetAppBundle:Event/Order:summary.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'summary'   => $summary,
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param Order     $order
     *
     * @return Response
     */
    public function proformaAction(Request $request, EventView $eventView, Sheet $sheet, Order $order)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

        if ($order->getSheet() !== $sheet) {
            throw $this->createNotFoundException();
        }

        $proforma = $this
            ->get('components.sheet.proforma_view_factory')
            ->createFromOrder($order, $request->getLocale());

        return $this->render('VimeetAppBundle:Event/Order:proforma.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'order'     => $order,
            'proforma'  => $proforma,
        ]);
    }
}
