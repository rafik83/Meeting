<?php

namespace Proximum\Vimeet\Application\Command\Transaction;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Transaction\TransactionConfirmedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateTransactionHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event           = EventFactory::createEvent();
        $type            = new Type($event);
        $user            = new User('test@test.fr', 'test', 'test', 'fr');
        $sheet           = new Sheet($event, $type, [], $user, new \DateTime());
        $transactionDate = new \DateTime();

        $transaction = new Transaction(
            $sheet,
            200,
            $transactionDate,
            Mode::PAYMENT_BANK_CARD,
            'transaction_O1',
            Transaction::STATE_PENDING,
            $sheet->getEvent()->getCurrency(),
            $user
        );

        $update        = new Update($transaction);
        $update->state = Transaction::STATE_PAID;

        $expectedTransaction = new Transaction(
            $sheet,
            200,
            $transactionDate,
            Mode::PAYMENT_BANK_CARD,
            'transaction_O1',
            Transaction::STATE_PAID,
            $sheet->getEvent()->getCurrency(),
            $user
        );

        $transactionRepository = $this->prophesize(TransactionRepositoryInterface::class);
        $eventDispatcher       = $this->prophesize(DelayedEventDispatcher::class);
        $transactionRepository->set($expectedTransaction)->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::TRANSACTION_CONFIRMED,
            new TransactionConfirmedEvent($user, $expectedTransaction)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::TRANSACTION_UPDATED,
            new TransactionUpdatedEvent($expectedTransaction)
        )->shouldBeCalled();

        $handler = new UpdateHandler($transactionRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle($update);
    }
}
