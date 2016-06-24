<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event\PaymentConditions;

use Proximum\Vimeet\Application\Command\Event\PaymentConditions\Update;
use Proximum\Vimeet\Application\Command\Event\PaymentConditions\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $dateTime2 = new \DateTime();
        $event = new Event();
        $event->getConfiguration()->updatePaymentConditions(false, $dateTime, 500, 50);

        $update = new Update($event);
        $update->allowDeposit       = true;
        $update->depositUntil       = $dateTime2;
        $update->minimumForDeposit  = 200;
        $update->deposit            = 90;

        $expectedEvent = new Event();
        $expectedEvent->getConfiguration()->updatePaymentConditions(true, $dateTime2, 200, 90);

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        $handler = new UpdateHandler($eventRepository->reveal());
        $handler->handle($update);
    }
}