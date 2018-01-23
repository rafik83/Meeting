<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\View\Dashboard\DashboardTransactionView;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class DashboardTransactionViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();

        // Expected
        $expectedView = new DashboardTransactionView(100, 25, 75);

        // Mock
        $balance = $this->prophesize(Balance::class);

        $balance->loadAllForEvent($event)->shouldBeCalled();

        $balance->getOrdersTotalWithoutVatForEvent()->shouldBeCalled()->willReturn(100);
        $balance->getTransactionsTotalPaidForEvent()->shouldBeCalled()->willReturn(75);
        $balance->getTotalRemainingToPayForEvent()->shouldBeCalled()->willReturn(25);

        $handler = new DashboardTransactionViewQueryHandler($balance->reveal());

        $view = $handler->handle(new DashboardTransactionViewQuery($event));

        $this->assertEquals($view, $expectedView);
    }
}
