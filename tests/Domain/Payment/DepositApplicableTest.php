<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Payment;

use Proximum\Vimeet\Domain\Payment\DepositApplicable;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DepositApplicableTest extends \PHPUnit_Framework_TestCase
{
    public function testIsApplicableFalse()
    {
        $event = EventFactory::createEvent();
        $event->getConfiguration()->updatePaymentConditions(
            false,
            new \DateTime('10/10/2010 10:10:10'),
            3000,
            20
        );
        $now   = new \DateTime();
        $total = 2000;


        $depositApplicable = new DepositApplicable();
        $this->assertFalse($depositApplicable->isApplicable($event, $now, $total));
    }


    public function testIsApplicableTrue()
    {
        $event = EventFactory::createEvent();
        $event->getConfiguration()->updatePaymentConditions(
            true,
            new \DateTime('10/10/2020 10:10:10'),
            200,
            20
        );
        $now   = new \DateTime();
        $total = 2000;


        $depositApplicable = new DepositApplicable();
        $this->assertTrue($depositApplicable->isApplicable($event, $now, $total));
    }

    public function testCalculateDeposit()
    {
        $event = EventFactory::createEvent();
        $now   = new \DateTime();
        $total = 2000;
        $event->getConfiguration()->updatePaymentConditions(
            true,
            new \DateTime('10/10/2020 10:10:10'),
            200,
            20
        );

        $depositApplicable = new DepositApplicable();
        $this->assertEquals(400, $depositApplicable->calculateDeposit($event, $now, $total));
    }

    public function testCalculateDepositWithNoDeposit()
    {
        $event = EventFactory::createEvent();
        $now   = new \DateTime();
        $total = 2000;
        $event->getConfiguration()->updatePaymentConditions(
            false,
            new \DateTime('10/10/2020 10:10:10'),
            200,
            20
        );

        $depositApplicable = new DepositApplicable();
        $this->assertEquals(2000, $depositApplicable->calculateDeposit($event, $now, $total));
    }
}
