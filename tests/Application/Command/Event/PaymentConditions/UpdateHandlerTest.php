<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\PaymentConditions;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Event\PaymentConditions\Update;
use Proximum\Vimeet\Application\Command\Event\PaymentConditions\UpdateHandler;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime  = new \DateTime();
        $dateTime2 = new \DateTime();
        $event = EventFactory::createEvent();
        $event->getConfiguration()->updatePaymentConditions([Mode::PAYMENT_BANK_CARD], false, $dateTime, 500, 50);

        $update = new Update($event);
        $update->allowDeposit       = true;
        $update->depositUntil       = $dateTime2;
        $update->minimumForDeposit  = 200;
        $update->deposit            = 90;
        $update->paymentModes       = [Mode::PAYMENT_BANK_CHECK, Mode::PAYMENT_BANK_TRANSFER];

        $expectedEvent = EventFactory::createEvent();
        $expectedEvent->getConfiguration()->updatePaymentConditions(
            [Mode::PAYMENT_BANK_CHECK, Mode::PAYMENT_BANK_TRANSFER], true, $dateTime2, 200, 90
        );

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        $handler = new UpdateHandler($eventRepository->reveal());
        $handler->handle($update);
    }
}
