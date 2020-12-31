<?php

namespace Proximum\Vimeet\Application\Command\Transaction;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Transaction\TransactionConfirmedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionCreatedEvent;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class CreateTransactionHandlerTest extends TestCase
{
    public function testCreatePaidTransactionHandle()
    {
        $event = EventFactory::createEvent();
        $sheet = SheetFactory::create($event);

        $transactionDate = new \DateTime();

        $create = new Create(
            $sheet,
            200,
            $transactionDate,
            Mode::PAYMENT_BANK_CARD,
            'transaction_O1',
            Transaction::STATE_PAID
        );

        $expectedTransaction = new Transaction(
            $sheet,
            200,
            $transactionDate,
            Mode::PAYMENT_BANK_CARD,
            'transaction_O1',
            Transaction::STATE_PAID,
            $sheet->getEvent()->getCurrency()
        );

        $transactionRepository = $this->prophesize(TransactionRepositoryInterface::class);
        $eventDispatcher       = $this->prophesize(DelayedEventDispatcher::class);

        $transactionRepository->add($expectedTransaction)->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::TRANSACTION_CONFIRMED,
            new TransactionConfirmedEvent($expectedTransaction->getUser(), $expectedTransaction)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::TRANSACTION_CREATED,
            new TransactionCreatedEvent($expectedTransaction)
        )->shouldBeCalled();

        $handler = new CreateHandler($transactionRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($create);
    }

    public function testCreateUnpaidTransactionHandle()
    {
        $event = EventFactory::createEvent();
        $sheet = SheetFactory::create($event);

        $transactionDate = new \DateTime();

        $create = new Create(
            $sheet,
            199,
            $transactionDate,
            Mode::PAYMENT_BANK_CARD,
            'transaction_O2',
            Transaction::STATE_PENDING
        );

        $expectedTransaction = new Transaction(
            $sheet,
            199,
            $transactionDate,
            Mode::PAYMENT_BANK_CARD,
            'transaction_O2',
            Transaction::STATE_PENDING,
            $sheet->getEvent()->getCurrency()
        );

        $transactionRepository = $this->prophesize(TransactionRepositoryInterface::class);
        $eventDispatcher       = $this->prophesize(DelayedEventDispatcher::class);

        $transactionRepository->add($expectedTransaction)->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::TRANSACTION_CONFIRMED,
            new TransactionConfirmedEvent($expectedTransaction->getUser(), $expectedTransaction)
        )->shouldNotBeCalled();

        $eventDispatcher->dispatch(
            Events::TRANSACTION_CREATED,
            new TransactionCreatedEvent($expectedTransaction)
        )->shouldBeCalled();

        $handler = new CreateHandler($transactionRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($create);
    }
}
