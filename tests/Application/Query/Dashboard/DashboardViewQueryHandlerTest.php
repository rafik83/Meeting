<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use Proximum\Vimeet\Application\Query\Dashboard\DashboardViewQuery;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardViewQueryHandler;
use Proximum\Vimeet\Application\View\Dashboard\DashboardView;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DashboardViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $query = new DashboardViewQuery($event);

        //Expected
        $dashboardViewExpected = new DashboardView();

        $dashboardViewExpected->totalOrders         = 200;
        $dashboardViewExpected->totalRemainingToPay = 100;
        $dashboardViewExpected->totalPaid           = 100;

        // Mock
        $balance = $this->prophesize(Balance::class);

        $balance->loadAllTransactions($event)
            ->shouldBeCalled()
        ;

        $balance->loadAllOrdersByEvent($event)
            ->shouldBeCalled()
        ;

        $balance->getOrdersTotal($event)
            ->shouldBeCalled()
            ->willReturn(200)
        ;

        $balance->getTransactionsTotalPaid($event)
            ->shouldBeCalled()
            ->willReturn(100)
        ;

        $balance->getOrdersTotalRemainingToPay($event)
            ->shouldBeCalled()
            ->willReturn(100)
        ;

        $handler = new DashboardViewQueryHandler($balance->reveal());

        $dashboardView = $handler->handle($query);

        $this->assertEquals($dashboardViewExpected, $dashboardView);
    }
}
