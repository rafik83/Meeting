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
use Proximum\Vimeet\Domain\View\OrderListView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends Controller
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request, Event $event, Sheet $sheet)
    {
        $orders = $this
            ->get('vimeet_infrastructure.repository.order_repository')
            ->paginate($request->query->getInt('page', 1), 20, $sheet);

        $orders->setItems(array_map(function (Order $order) use ($request) {
            return new OrderListView(
                $order->getId(),
                $order->getCreatedAt(),
                $this->getOrderAmount($order, $request->getLocale()),
                $order->getState(),
                $order->getPaymentMode()
            );
        }, $orders->getItems()));

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetInfo($sheet);

        return $this->render('VimeetAppBundle:Admin/Order:list.html.twig', [
            'event'      => $event,
            'sheet'      => $sheet,
            'sheet_info' => $sheetInfo,
            'orders'     => $orders,
        ]);
    }

    /**
     * @param Order  $order
     * @param string $locale
     *
     * @return int
     */
    private function getOrderAmount(Order $order, $locale)
    {
        $template = $this
            ->get('vimeet_infrastructure.application.components.product.product_builder')
            ->createFromOrder($order);

        $cart = $this
            ->get('vimeet_infrastructure.application.components.cart.cart_builder')
            ->generate($template, $order->getPackageData(), $locale);

        return $cart->getTotal();
    }
}
