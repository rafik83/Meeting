<?php

namespace Proximum\Vimeet\Tests\Application\Query\Payment;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Payment\PaymentConditionsViewQuery;
use Proximum\Vimeet\Application\Query\Payment\PaymentConditionsViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Configuration;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Payment\PaymentConditionsView;

class PaymentConditionsViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $sheet    = $this->prophesize(Sheet::class);
        $type     = $this->prophesize(Type::class);
        $paymentConditions = new Type\PaymentConditions(
            $type->reveal(),
            [Mode::PAYMENT_PAYPAL, Mode::PAYMENT_BANK_CHECK],
            true,
            $dateTime,
            1000,
            40
        );

        $sheet->getType()->willReturn($type->reveal());
        $type->getPaymentConditions()->willReturn($paymentConditions);

        $expected = new PaymentConditionsView(
            [Mode::PAYMENT_PAYPAL, Mode::PAYMENT_BANK_CHECK],
            true,
            $dateTime,
            1000,
            40
        );

        $handler = new PaymentConditionsViewQueryHandler();
        $result = $handler->handle(new PaymentConditionsViewQuery($sheet->reveal()));

        $this->assertEquals($expected, $result);
    }

    public function testHandleNoPaymentConditionsOnType()
    {
        $dateTime = new \DateTime();
        $sheet    = $this->prophesize(Sheet::class);
        $type     = $this->prophesize(Type::class);
        $event    = $this->prophesize(Event::class);
        $configuration = new Configuration('#123123', '#123123', '#123123');
        $configuration->updatePaymentConditions(
            [Mode::PAYMENT_PAYPAL, Mode::PAYMENT_BANK_CHECK],
            true,
            $dateTime,
            1000,
            40
        );
        $event->getConfiguration()->willReturn($configuration);

        $sheet->getType()->willReturn($type->reveal());
        $sheet->getEvent()->willReturn($event->reveal());
        $type->getPaymentConditions()->willReturn(null);

        $expected = new PaymentConditionsView(
            [Mode::PAYMENT_PAYPAL, Mode::PAYMENT_BANK_CHECK],
            true,
            $dateTime,
            1000,
            40
        );

        $handler = new PaymentConditionsViewQueryHandler();
        $result = $handler->handle(new PaymentConditionsViewQuery($sheet->reveal()));

        $this->assertEquals($expected, $result);
    }
}
