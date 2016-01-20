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
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BillingController extends Controller
{

    /**
     * @ParamConverter(
     *   "sheet",
     *   class="Proximum\Vimeet\Domain\Model\Sheet",
     *   options={"id" = "sheet_id"}
     * )
     *
     * @param Request $request
     * @param Event   $event
     * @param Sheet   $sheet
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event, Sheet $sheet)
    {
        // Sheet
        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetInfo($sheet);

        // Orders
        $orders = $this
            ->get('vimeet_infrastructure.repository.order_repository')
            ->findBySheet($sheet);

        $orders = array_map(function (Order $order) use ($request) {
            return $this->get('components.sheet.order_view_factory')->createFromOrder($order, $request->getLocale());
        }, $orders);

        // Transactions
        $transactions = $this->get('repository.transaction')->findBySheet($sheet);

        return $this->render('VimeetAppBundle:Admin/Billing:list.html.twig', [
            'event'        => $event,
            'sheet'        => $sheet,
            'sheet_info'   => $sheetInfo,
            'orders'       => $orders,
            'transactions' => $transactions,
        ]);
    }
}