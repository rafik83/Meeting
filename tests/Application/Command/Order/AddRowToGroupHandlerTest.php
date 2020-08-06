<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Order;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Order\AddRowToGroup;
use Proximum\Vimeet\Application\Command\Order\AddRowToGroupHandler;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class AddRowToGroupHandlerTest extends TestCase
{
    public function testHandle()
    {
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $order = $this->prophesize(Order::class);
        $row = Row::createCustomRowToGroup(
            $order->reveal(),
            2,
            5,
            'toto',
            100,
            20
        );
        $order->getGroups()->willReturn([5 => 'foo']);
        $order->getVatRate()->willReturn(20);
        $order->addCustomRow($row)->shouldBeCalled();
        $orderRepository->set($order->reveal())->shouldBeCalled();

        $add = new AddRowToGroup($order->reveal(), 5);
        $add->groupId = 5;
        $add->price = 100;
        $add->label = 'toto';
        $add->quantity = 2;

        // Handler
        $handler = new AddRowToGroupHandler($orderRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($add);
    }
}
