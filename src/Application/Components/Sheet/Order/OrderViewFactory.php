<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

use Proximum\Vimeet\Application\Components\Sheet\Order\Specification\VatApplicable;
use Proximum\Vimeet\Domain\Model\Order;

class OrderViewFactory
{
    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var VatApplicable
     */
    private $vatApplicable;

    /**
     * OrderViewFactory constructor.
     *
     * @param GroupFactory  $groupFactory
     * @param VatApplicable $vatApplicable
     */
    public function __construct(GroupFactory $groupFactory, VatApplicable $vatApplicable)
    {
        $this->groupFactory  = $groupFactory;
        $this->vatApplicable = $vatApplicable;
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
            $this->vatApplicable->onOrder($order),
            $order->getVatRate(),
            $this->groupFactory->createGroupsFromArray($order->getPackageTemplate(), $order->getPackageData(), $locale)
        );
    }
}
