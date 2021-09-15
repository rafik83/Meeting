<?php

namespace Proximum\Vimeet\Tests\Domain\Transaction;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Transaction\TransactionConfirmedEvent;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Domain\Transaction\TransactionManager;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class TransactionManagerTest extends TestCase
{
    public function testSetPaid()
    {
        $dateTime    = new \DateTime();
        $event       = EventFactory::createEvent();
        $type        = new Type($event);
        $owner       = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet       = new Sheet($event, $type, [], $owner, $dateTime);
        $transaction = new Transaction(
            $sheet,
            20,
            $dateTime,
            Mode::PAYMENT_PAYPAL,
            '',
            Transaction::STATE_PENDING,
            'EUR',
            $owner
        );

        $transactionRepository = $this->prophesize(TransactionRepositoryInterface::class);
        $eventDispatcher       = $this->prophesize(DelayedEventDispatcher::class);

        $transactionRepository->set($transaction)->shouldBeCalled();

        $transactionConfirmEvent = new TransactionConfirmedEvent($transaction->getUser(), $transaction);
        $eventDispatcher->dispatch(Events::TRANSACTION_CONFIRMED, $transactionConfirmEvent)->shouldBeCalled();

        $transactionManager = new TransactionManager($transactionRepository->reveal(), $eventDispatcher->reveal());
        $transactionManager->setPaid($transaction);
    }
}
