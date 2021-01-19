<?php

namespace Proximum\Vimeet\Tests\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Payment\DepositApplicable;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Payment\PaymentConditionsView;

class DepositApplicableTest extends TestCase
{
    public function testIsApplicableFalseDatePassed()
    {
        $paymentConditionsView = new PaymentConditionsView(
            [Mode::PAYMENT_BANK_CARD],
            true,
            new \DateTime('2010-10-10 10:10:10'),
            3000,
            20
        );
        $now   = new \DateTime();
        $total = 2000;

        $depositApplicable = new DepositApplicable();
        $this->assertFalse($depositApplicable->isApplicable($paymentConditionsView, $now, $total));
    }

    public function testIsApplicableFalseTotalTooLow()
    {
        $paymentConditionsView = new PaymentConditionsView(
            [Mode::PAYMENT_BANK_CARD],
            true,
            new \DateTime('2010-10-10 10:10:10'),
            200,
            20
        );
        $now   = new \DateTime();
        $total = 100;

        $depositApplicable = new DepositApplicable();
        $this->assertFalse($depositApplicable->isApplicable($paymentConditionsView, $now, $total));
    }

    public function testIsApplicableFalseDepositNotAllowed()
    {
        $paymentConditionsView = new PaymentConditionsView(
            [Mode::PAYMENT_BANK_CARD],
            false,
            new \DateTime('2010-10-10 10:10:10'),
            200,
            20
        );
        $now   = new \DateTime();
        $total = 100;

        $depositApplicable = new DepositApplicable();
        $this->assertFalse($depositApplicable->isApplicable($paymentConditionsView, $now, $total));
    }

    public function testIsApplicableTrue()
    {
        $paymentConditionsView = new PaymentConditionsView(
            [Mode::PAYMENT_BANK_CARD],
            true,
            new \DateTime('2020-10-10 10:10:10'),
            200,
            20
        );
        $now   = new \DateTime('2017-10-10 10:10:10');
        $total = 2000;

        $depositApplicable = new DepositApplicable();
        $this->assertTrue($depositApplicable->isApplicable($paymentConditionsView, $now, $total));
    }

    public function testCalculateDeposit()
    {
        $now   = new \DateTime('2017-10-10 10:10:10');
        $total = 2000;
        $paymentConditionsView = new PaymentConditionsView(
            [Mode::PAYMENT_BANK_CARD],
            true,
            new \DateTime('2020-10-10 10:10:10'),
            200,
            20
        );

        $depositApplicable = new DepositApplicable();
        $this->assertEquals(400, $depositApplicable->calculateDeposit($paymentConditionsView, $now, $total));
    }

    public function testCalculateDepositWithNoDeposit()
    {
        $now   = new \DateTime('2017-10-10 10:10:10');
        $total = 2000;
        $paymentConditionsView = new PaymentConditionsView(
            [Mode::PAYMENT_BANK_CARD],
            false,
            new \DateTime('2020-10-10 10:10:10'),
            200,
            20
        );

        $depositApplicable = new DepositApplicable();
        $this->assertEquals(2000, $depositApplicable->calculateDeposit($paymentConditionsView, $now, $total));
    }
}
