<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

use Proximum\Vimeet\Domain\Model\Order;

class OrderViewFactory
{
    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * OrderViewFactory constructor.
     *
     * @param GroupFactory $groupFactory
     */
    public function __construct(GroupFactory $groupFactory)
    {
        $this->groupFactory = $groupFactory;
    }

    /**
     * @param Order  $order
     * @param string $locale
     *
     * @return OrderView
     */
    public function createFromOrder(Order $order, $locale)
    {
        return new OrderView(
            $order->getId(),
            $order->getId(),
            $order->getCreatedAt(),
            $order->getState(),
            $order->getPaymentMode(),
            $order->getVatMode(),
            $order->getVatRate(),
            $this->groupFactory->createGroupsFromArray($order->getPackageTemplate(), $order->getPackageData(), $locale)
        );
    }
}
