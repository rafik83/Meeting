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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @ParamConverter(
     *   "order",
     *   class="Proximum\Vimeet\Domain\Model\Order",
     *   options={"id" = "order_id"}
     * )
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param Order     $order
     *
     * @return Response
     */
    public function proFormaAction(Request $request, EventView $eventView, Sheet $sheet, Order $order)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

        if ($order->getSheet() !== $sheet) {
            throw $this->createNotFoundException();
        }

        $template = $this->get('vimeet_infrastructure.application.components.product.product_builder')
            ->create($order->getPackageTemplate());
        $cart     = $this->get('vimeet_infrastructure.application.components.cart.cart_builder')
            ->generate(
                $template,
                $order->getPackageData(),
                $request->getLocale()
            )
        ;

        return $this->render('VimeetAppBundle:Event/Order:proForma.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'cart'      => $cart,
            'order'     => $order,
        ]);
    }
}
