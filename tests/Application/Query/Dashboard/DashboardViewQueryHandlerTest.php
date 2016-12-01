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
        $now              = new \DateTime();
        $event            = EventFactory::createEvent();
        $type             = new Type($event);
        $user             = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet            = new Sheet($event, $type, [], $user, $now);
        $orderBillingInfo = new Order\BillingInfo(
            'gender',
            'lastname',
            'firstname',
            'function',
            'phone',
            'mobile',
            'company',
            'email@email.com',
            new Address('street', 'zipcode', 'city', 'FR'),
            'vatNumber'
        );

        $order = new Order($sheet, false, $orderBillingInfo, '', $now);
        $sheet->addOrder($order);

        $query = new DashboardViewQuery($event);

        // Mock
        $balance         = $this->prophesize(Balance::class);

        $balance->loadAllTransactions()
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $balance->loadAllOrdersByEvent()
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $balance->getOrdersTotal($sheet)
            ->shouldBeCalled()
            ->willReturn(100)
        ;

        $balance->getTransactionsTotalPaid($sheet)
            ->shouldBeCalled()
            ->willReturn(100)
        ;

        $balance->getOrdersTotalRemainingToPay($sheet)
            ->shouldBeCalled()
            ->willReturn(100)
        ;

        $handler = new DashboardViewQueryHandler($balance->reveal());

        $handler->handle($query);
    }
}
