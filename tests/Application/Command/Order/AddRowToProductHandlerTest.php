<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Order;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Order\AddRowToProduct;
use Proximum\Vimeet\Application\Command\Order\AddRowToProductHandler;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AddRowToProductTest extends TestCase
{
    public function testHandle()
    {
        $event    = EventFactory::createEvent();
        $product  = Product::createOption($event, 'Option A', 'a.jpg', 100, 2, 4, 3, false);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $order           = $this->prophesize(Order::class);
        $parentRow = new Order\Row(
            $order->reveal(),
            1,
            $product,
            5,
            "label",
            12.5
        );

        $row = Order\Row::createCustomRowToProduct(
            $order->reveal(),
            $parentRow,
            "label",
            1,
            12.5
        );

        $orderRepository->set($order->reveal())->shouldBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $add = new AddRowToProduct($order->reveal(), $row);

        // Handler
        $handler = new AddRowToProductHandler($orderRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($add);
    }
}
