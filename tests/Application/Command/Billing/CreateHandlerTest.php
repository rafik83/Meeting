<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Billing;

use Proximum\Vimeet\Application\Command\Billing\CreateOrder;
use Proximum\Vimeet\Application\Command\Billing\CreateOrderHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {

        //Context
        $event = new Event();
        $type  = new Type($event);
        $sheet = new Sheet($event, $type, [], []);

        $date = new \DateTime('2016-01-14 08:00:00');

        //Command
        $createOrder              = new CreateOrder($sheet, 'test', 'test', [], [], [], $date);
        $createOrder->paymentMode = 'test';

        //Expected
        $expectedOrder = new Order($sheet, 'test', 'test', [], [], [], $date, 'test');


        //Mock
        $billingRepository = $this->prophesize(OrderRepositoryInterface::class);
        $billingRepository->add($expectedOrder)->shouldBeCalled();


        //Handler
        $handler = new CreateOrderHandler($billingRepository->reveal());
        $handler->handle($createOrder);


    }
}

