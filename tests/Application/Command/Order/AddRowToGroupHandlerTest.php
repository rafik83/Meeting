<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Order;

use Proximum\Vimeet\Application\Command\Order\AddRowToGroup;
use Proximum\Vimeet\Application\Command\Order\AddRowToGroupHandler;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class AddRowToGroupTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $order           = $this->prophesize(Order::class);

        $orderRepository->set($order->reveal())->shouldBeCalled();

        $add = new AddRowToGroup($order->reveal(), '5');

        // Handler
        $handler = new AddRowToGroupHandler($orderRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($add);
    }
}
