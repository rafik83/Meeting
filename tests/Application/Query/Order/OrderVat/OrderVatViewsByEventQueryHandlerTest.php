<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Order\OrderVat;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsByEventQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsByEventQueryHandler;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\OrderVatView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class OrderVatViewsByEventQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $now = new \DateTime();

        $event  = EventFactory::createEvent();
        $sheet1 = SheetFactory::create($event, UserFactory::create('user1@example.net'));
        $sheet2 = SheetFactory::create($event, UserFactory::create('user2@example.net'));
        $order1 = new Order($sheet1, [], $now);
        $order2 = new Order($sheet2, [], $now);

        $orderRepositoryInterface = $this->prophesize(OrderRepositoryInterface::class);
        $orderVatViewQueryHandler = $this->prophesize(OrderVatViewQueryHandler::class);

        $orderVatViewsByEventQueryHandler = new OrderVatViewsByEventQueryHandler(
            $orderRepositoryInterface->reveal(),
            $orderVatViewQueryHandler->reveal()
        );

        $orderRepositoryInterface->findByEventAndEnabledSheets($event)->shouldBeCalled()->willReturn(
            [
                $order1,
                $order2
            ]
        );

        $orderVatView1 = $this->prophesize(OrderVatView::class);
        $orderVatView2 = $this->prophesize(OrderVatView::class);

        $orderVatViewQueryHandler->handle(new OrderVatViewQuery($order1))->shouldBeCalled()->willReturn($orderVatView1->reveal());
        $orderVatViewQueryHandler->handle(new OrderVatViewQuery($order2))->shouldBeCalled()->willReturn($orderVatView2->reveal());

        $orderVatViews = $orderVatViewsByEventQueryHandler->handle(new OrderVatViewsByEventQuery($event));

        $this->assertEquals([$orderVatView1->reveal(), $orderVatView2->reveal()], $orderVatViews);
    }
}
