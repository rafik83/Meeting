<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Notification;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Notification\Transaction\TransactionPendingViewQuery;
use Proximum\Vimeet\Application\Query\Notification\Transaction\TransactionPendingViewQueryHandler;
use Proximum\Vimeet\Application\View\Notification\NotificationView;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Notification\Notification;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class TransactionPendingViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
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

        $router   = $this->prophesize(RouterInterface::class);

        $router->generate('event_order_list', ['sheet' => $sheet->getId()])
            ->shouldBeCalled()
            ->willReturn('event_order_list_path');

        $handler = new TransactionPendingViewQueryHandler($router->reveal(), new \DateTime());

        $notificationView = $handler->handle(new TransactionPendingViewQuery($transaction));

        $this->assertEquals($notificationView, $expectedNotificationView);
    }
}
