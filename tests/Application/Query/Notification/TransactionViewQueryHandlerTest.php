<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Notification;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Notification\Transaction\TransactionNotificationViewQuery;
use Proximum\Vimeet\Application\Query\Notification\Transaction\TransactionNotificationViewQueryHandler;
use Proximum\Vimeet\Application\Query\Notification\Transaction\TransactionPaidViewQuery;
use Proximum\Vimeet\Application\Query\Notification\Transaction\TransactionPaidViewQueryHandler;
use Proximum\Vimeet\Application\Query\Notification\Transaction\TransactionPendingViewQuery;
use Proximum\Vimeet\Application\Query\Notification\Transaction\TransactionPendingViewQueryHandler;
use Proximum\Vimeet\Application\View\Notification\NotificationView;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Notification\Notification;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class TransactionViewQueryHandlerTest extends TestCase
{
    public function testHandleNotificationPending()
    {
        $datetime    = new \DateTime();
        $event       = EventFactory::createEvent();
        $user        = new User('user@gmail.com', 'salt', 'pasword', 'fr');
        $sheet       = SheetFactory::create($event, $user, $datetime);
        $transaction = new Transaction($sheet, 100.0, $datetime, Mode::PAYMENT_BANK_CHECK, '', Transaction::STATE_PENDING, 'EUR', $user);

        // Expected view
        $expectedNotificationView = new NotificationView(
            $datetime,
            Category::BILLING_ICON,
            Notification::CATEGORY_TRANSACTION,
            'notification.transaction.pending',
            'event_order_list_path',
            Notification::PRIORITY_IMPORTANT,
            [
                '%amount%'   => 100.0,
                '%currency%' => 'EUR',
            ]
        );

        $router = $this->prophesize(RouterInterface::class);

        $router->generate('event_order_list', ['sheet' => $sheet->getId()])
            ->shouldBeCalled()
            ->willReturn('event_order_list_path');

        $handler = new TransactionPendingViewQueryHandler($router->reveal(), new \DateTime());

        $notificationView = $handler->handle(new TransactionPendingViewQuery($transaction));

        $this->assertEquals($notificationView, $expectedNotificationView);
    }

    public function testHandleNotificationPaid()
    {
        $datetime    = new \DateTime();
        $event       = EventFactory::createEvent();
        $user        = new User('user@gmail.com', 'salt', 'pasword', 'fr');
        $sheet       = SheetFactory::create($event, $user, $datetime);
        $transaction = new Transaction($sheet, 100.0, $datetime, Mode::PAYMENT_BANK_CHECK, '', Transaction::STATE_PAID, 'EUR', $user);

        // Expected view
        $expectedNotificationView = new NotificationView(
            $datetime,
            Category::BILLING_ICON,
            Notification::CATEGORY_TRANSACTION,
            'notification.transaction.paid',
            'event_order_list_path',
            Notification::PRIORITY_NONE,
            [
                '%amount%'   => 100.0,
                '%currency%' => 'EUR',
            ]
        );

        $router = $this->prophesize(RouterInterface::class);

        $router->generate('event_order_list', ['sheet' => $sheet->getId()])
            ->shouldBeCalled()
            ->willReturn('event_order_list_path');

        $handler = new TransactionPaidViewQueryHandler($router->reveal(), new \DateTime());

        $notificationView = $handler->handle(new TransactionPaidViewQuery($transaction));

        $this->assertEquals($notificationView, $expectedNotificationView);
    }

    public function testHandle()
    {
        $datetime        = new \DateTime();
        $event           = EventFactory::createEvent();
        $user            = new User('user@gmail.com', 'salt', 'pasword', 'fr');
        $sheet           = SheetFactory::create($event, $user, $datetime);
        $transaction     = new Transaction($sheet, 100.0, $datetime, Mode::PAYMENT_BANK_CHECK, '', Transaction::STATE_PENDING, 'EUR', $user);
        $paidTransaction = new Transaction($sheet, 100.0, $datetime, Mode::PAYMENT_BANK_CHECK, '', Transaction::STATE_PAID, 'EUR', $user);

        // Mock
        $transactionRepository              = $this->prophesize(TransactionRepositoryInterface::class);
        $transactionPendingViewQueryHandler = $this->prophesize(TransactionPendingViewQueryHandler::class);
        $balance = $this->prophesize(Balance::class);
        $transactionPaidViewQueryHandler    = $this->prophesize(TransactionPaidViewQueryHandler::class);

        $balance->getBalance($sheet)->shouldBeCalled()->willReturn(100.0);
        $transactionRepository->findPending($sheet)->shouldBeCalled()->willReturn([$transaction]);
        $transactionRepository->findPaid($sheet)->shouldBeCalled()->willReturn([$paidTransaction]);

        $transactionPendingViewQueryHandler
            ->handle(new TransactionPendingViewQuery($transaction))
            ->shouldBeCalled();

        $handler = new TransactionNotificationViewQueryHandler(
            $balance->reveal(),
            $transactionRepository->reveal(),
            $transactionPendingViewQueryHandler->reveal(),
            $transactionPaidViewQueryHandler->reveal()
        );

        $handler->handle(new TransactionNotificationViewQuery($sheet));
    }
}
