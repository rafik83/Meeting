<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Order\ProFormaQuery;
use Proximum\Vimeet\Application\Query\Order\SummaryQuery;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function listAction(EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $balance = $this->get('order.balance');
        $orders  = $balance->getOrders($sheet);

        if ($eventDomain->getEvent() !== $sheet->getEvent()
            || !$sheet->hasUser($this->getUser())
            || !$sheet->getPackage()->isPassable()
            || empty($orders)
        ) {
            throw $this->createNotFoundException('This page is not accessible by this user');
        }

        return $this->render('EventBundle:Order:list.html.twig', [
            'event'          => $eventDomain->getEvent(),
            'orders'         => $orders,
            'sheet'          => $sheet,
            'transactions'   => $balance->getTransactions($sheet),
            'remainingToPay' => $balance->getRemainingToPay($sheet),
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Order       $order
     *
     * @return Response
     */
    public function proFormaAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Order $order)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if ($eventDomain->getEvent() !== $sheet->getEvent()
            || !$sheet->hasUser($this->getUser())
            || !$sheet->getPackage()->isPassable()
            || $order->getSheet() !== $sheet
        ) {
            throw $this->createNotFoundException('This page is not accessible by this user');
        }

        $view = $this->get('tactician.commandbus.query')->handle(
            new ProFormaQuery(
                $sheet,
                $order,
                $request->getLocale()
            )
        );

        return $this->render('EventBundle:Order:pro_forma.html.twig', [
            'event'     => $eventDomain->getEvent(),
            'pro_forma' => $view,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function summaryTotalAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if ($eventDomain->getEvent() !== $sheet->getEvent()
            || !$sheet->hasUser($this->getUser())
            || !$sheet->getPackage()->isPassable()
            || count($sheet->getOrders()) === 0
        ) {
            throw $this->createNotFoundException('This page is not accessible by this user');
        }

        $orders = $this->get('vimeet_infrastructure.repository.order_repository')
            ->findBySheet($sheet);

        $orderMerger = $this->get('order.merger');
        $order       = $orderMerger->merge($orders);

        $view = $this->get('tactician.commandbus.query')->handle(new SummaryQuery(
            $sheet,
            $order,
            $request->getLocale()
        ));

        return $this->render('EventBundle:Order/SummaryTotal:summaryTotal.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'view'  => $view,
        ]);
    }
}
