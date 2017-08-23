<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Order;

use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsByEventQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsByEventQueryHandler;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsBySheetIdsQueryHandler;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsBySheetQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsBySheetQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Domain\View\OrderVatView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use PHPUnit\Framework\TestCase;

class BalanceTest extends TestCase
{
    /** @var Event */
    private $event;

    /** @var Sheet */
    private $sheet;

    /** @var OrderVatViewsByEventQueryHandler */
    private $orderVatViewsByEventQueryHandler;

    /** @var OrderVatViewsBySheetQueryHandler */
    private $orderVatViewsBySheetQueryHandler;

    /** @var OrderVatViewsBySheetIdsQueryHandler */
    private $orderVatViewsBySheetIdsQueryHandler;

    /** @var TransactionRepositoryInterface */
    private $transactionRepository;

    /** @var Balance */
    private $balance;

    public function setUp()
    {
        $this->event = EventFactory::createEvent();
        $this->sheet = SheetFactory::create($this->event);

        $this->orderVatViewsByEventQueryHandler = $this->prophesize(OrderVatViewsByEventQueryHandler::class);
        $this->orderVatViewsBySheetQueryHandler = $this->prophesize(OrderVatViewsBySheetQueryHandler::class);
        $this->orderVatViewsBySheetIdsQueryHandler = $this->prophesize(OrderVatViewsBySheetIdsQueryHandler::class);
        $this->transactionRepository = $this->prophesize(TransactionRepositoryInterface::class);

        $this->balance = new Balance(
            $this->orderVatViewsByEventQueryHandler->reveal(),
            $this->orderVatViewsBySheetQueryHandler->reveal(),
            $this->orderVatViewsBySheetIdsQueryHandler->reveal(),
            $this->transactionRepository->reveal()
        );
    }

    public function testLoadAllTransactions()
    {
        $this->transactionRepository->findByEventAndEnabledSheets($this->event)->shouldBeCalled()->willReturn([]);

        $this->balance->loadAllTransactions($this->event);
    }

    public function testLoadAllOrderVatViews()
    {
        $this->orderVatViewsByEventQueryHandler
            ->handle(new OrderVatViewsByEventQuery($this->event))
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->balance->loadAllOrderVatViews($this->event);
    }

    public function testLoadAllForEvent()
    {
        $this->transactionRepository
            ->findByEventAndEnabledSheets($this->event)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->orderVatViewsByEventQueryHandler
            ->handle(new OrderVatViewsByEventQuery($this->event))
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->balance->loadAllForEvent($this->event);
    }

    public function testGetOrderVatViews()
    {
        $orderVatView = $this->prophesize(OrderVatView::class);

        $this->orderVatViewsBySheetQueryHandler
            ->handle(new OrderVatViewsBySheetQuery($this->sheet))
            ->shouldBeCalled()
            ->willReturn([$orderVatView->reveal()]);

        $orderVatViews = $this->balance->getOrderVatViews($this->sheet);
        $expectedOrderVatViews = [$orderVatView->reveal()];

        $this->assertEquals($expectedOrderVatViews, $orderVatViews);
    }

    public function testGetNotCancelledOrders()
    {
        $orderVatViewCancelled = $this->prophesize(OrderVatView::class);
        $orderVatViewCancelled->isCancelled = true;

        $orderVatViewNotCancelled = $this->prophesize(OrderVatView::class);
        $orderVatViewNotCancelled->isCancelled = false;

        $this->orderVatViewsBySheetQueryHandler
            ->handle(new OrderVatViewsBySheetQuery($this->sheet))
            ->shouldBeCalled()
            ->willReturn([0 => $orderVatViewCancelled->reveal(), 1 => $orderVatViewNotCancelled->reveal()]);

        $orderVatViews = $this->balance->getNotCancelledOrderVatViews($this->sheet);

        // It is an array_filter, array keys are preserved.
        $expectedOrderVatViews = [1 => $orderVatViewNotCancelled->reveal()];

        $this->assertEquals($expectedOrderVatViews, $orderVatViews);
    }

    public function testGetTransactions()
    {
        $transaction = $this->prophesize(Transaction::class);
        $this->transactionRepository->findBySheet($this->sheet)->shouldBeCalled()->willReturn([$transaction->reveal()]);

        $transactions = $this->balance->getTransactions($this->sheet);
        $expectedTransactions = [$transaction->reveal()];

        $this->assertEquals($expectedTransactions, $transactions);
    }

    public function testGetTotal()
    {
        $now = new \DateTime();
        $orderVatView1 = new OrderVatView('Order1', 1, 1, true, 20, 'ati', 'EUR', false, 1000, 200, 1200, $now);
        $orderVatView2 = new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', false, 200, 40, 240, $now);
        $orderVatViewCancelled = new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', true, 2000, 400, 2400, $now);

        $this->orderVatViewsBySheetQueryHandler
            ->handle(new OrderVatViewsBySheetQuery($this->sheet))
            ->shouldBeCalled()
            ->willReturn([$orderVatView1, $orderVatView2, $orderVatViewCancelled]);

        $total = $this->balance->getTotal($this->sheet);

        $this->assertEquals(1440, $total);
    }

    public function testGetTotalWithoutVat()
    {
        $now = new \DateTime();
        $orderVatView1 = new OrderVatView('Order1', 1, 1, true, 20, 'ati', 'EUR', false, 1000, 200, 1200, $now);
        $orderVatView2 = new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', false, 200, 40, 240, $now);
        $orderVatViewCancelled = new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', true, 2000, 400, 2400, $now);

        $this->orderVatViewsBySheetQueryHandler
            ->handle(new OrderVatViewsBySheetQuery($this->sheet))
            ->shouldBeCalled()
            ->willReturn([$orderVatView1, $orderVatView2, $orderVatViewCancelled]);

        $totalWithoutVat = $this->balance->getTotalWithoutVat($this->sheet);

        $this->assertEquals(1200, $totalWithoutVat);
    }

    public function testGetPositiveBalance()
    {
        $now = new \DateTime();

        $this->orderVatViewsBySheetQueryHandler
            ->handle(new OrderVatViewsBySheetQuery($this->sheet))
            ->shouldBeCalled()
            ->willReturn(
                [
                    new OrderVatView('Order1', 1, 1, true, 20, 'ati', 'EUR', false, 2000, 400, 2400, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', false, 1000, 200, 1200, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', true, 2000, 400, 2400, $now),
                ]
            );

        $this->transactionRepository->findBySheet($this->sheet)->shouldBeCalled()->willReturn(
            [
                new Transaction($this->sheet, 20, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 1, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 16, $now, 'paypal', '', 'pending', 'EUR'),
            ]
        );

        $balance = $this->balance->getBalance($this->sheet);

        $this->assertEquals(1500, $balance);
    }

    public function testGetNegativeBalance()
    {
        $now = new \DateTime();

        $this->orderVatViewsBySheetQueryHandler
            ->handle(new OrderVatViewsBySheetQuery($this->sheet))
            ->shouldBeCalled()
            ->willReturn(
                [
                    new OrderVatView('Order1', 1, 1, true, 20, 'ati', 'EUR', false, 2000, 400, 2400, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', false, 1000, 200, 1200, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', true, 2000, 400, 2400, $now),
                ]
            );

        $this->transactionRepository->findBySheet($this->sheet)->shouldBeCalled()->willReturn(
            [
                new Transaction($this->sheet, 200, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 100, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 150, $now, 'paypal', '', 'pending', 'EUR'),
            ]
        );

        $balance = $this->balance->getBalance($this->sheet);

        $this->assertEquals(-26400, $balance);
    }

    public function testGetPositiveBalanceForRemainingToPay()
    {
        $now = new \DateTime();

        $this->orderVatViewsBySheetQueryHandler
            ->handle(new OrderVatViewsBySheetQuery($this->sheet))
            ->shouldBeCalled()
            ->willReturn(
                [
                    new OrderVatView('Order1', 1, 1, true, 20, 'ati', 'EUR', false, 2000, 400, 2400, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', false, 1000, 200, 1200, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', true, 2000, 400, 2400, $now),
                ]
            );

        $this->transactionRepository->findBySheet($this->sheet)->shouldBeCalled()->willReturn(
            [
                new Transaction($this->sheet, 20, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 1, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 16, $now, 'paypal', '', 'pending', 'EUR'),
            ]
        );

        $remainingToPay = $this->balance->getRemainingToPay($this->sheet);

        $this->assertEquals(1500, $remainingToPay);
    }

    public function testGetNegativeBalanceForRemainingToPay()
    {
        $now = new \DateTime();

        $this->orderVatViewsBySheetQueryHandler
            ->handle(new OrderVatViewsBySheetQuery($this->sheet))
            ->shouldBeCalled()
            ->willReturn(
                [
                    new OrderVatView('Order1', 1, 1, true, 20, 'ati', 'EUR', false, 2000, 400, 2400, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', false, 1000, 200, 1200, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', true, 2000, 400, 2400, $now),
                ]
            );

        $this->transactionRepository->findBySheet($this->sheet)->shouldBeCalled()->willReturn(
            [
                new Transaction($this->sheet, 200, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 100, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 150, $now, 'paypal', '', 'pending', 'EUR'),
            ]
        );

        $remainingToPay = $this->balance->getRemainingToPay($this->sheet);

        $this->assertEquals(0, $remainingToPay);
    }

    public function testGetTotalPaid()
    {
        $now = new \DateTime();

        $this->transactionRepository->findBySheet($this->sheet)->shouldBeCalled()->willReturn(
            [
                new Transaction($this->sheet, 200, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 100, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 150, $now, 'paypal', '', 'pending', 'EUR'),
            ]
        );

        $totalPaid = $this->balance->getTotalPaid($this->sheet);

        $this->assertEquals(30000, $totalPaid);
    }

    public function testGetNotCancelledOrderVatViewsFromEvent()
    {
        $now = new \DateTime();

        $this->orderVatViewsByEventQueryHandler
            ->handle(new OrderVatViewsByEventQuery($this->event))
            ->shouldBeCalled()
            ->willReturn(
                [
                    new OrderVatView('Order1', 1, 1, true, 20, 'ati', 'EUR', false, 2000, 400, 2400, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', false, 1000, 200, 1200, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', true, 2000, 400, 2400, $now),
                ]
            )
        ;

        $this->balance->loadAllOrderVatViews($this->event);
        $notCancelledOrdersFromEvent = $this->balance->getNotCancelledOrderVatViewsFromEvent();

        $this->assertEquals(
            [
                new OrderVatView('Order1', 1, 1, true, 20, 'ati', 'EUR', false, 2000, 400, 2400, $now),
                new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', false, 1000, 200, 1200, $now),
            ],
            $notCancelledOrdersFromEvent
        );
    }

    public function testGetTransactionsTotalPaidForEvent()
    {
        $now = new \DateTime();

        $this->transactionRepository->findByEventAndEnabledSheets($this->event)->shouldBeCalled()->willReturn(
            [
                new Transaction($this->sheet, 200, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 100, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 150, $now, 'paypal', '', 'pending', 'EUR'),
            ]
        );

        $this->balance->loadAllTransactions($this->event);
        $totalPaid = $this->balance->getTransactionsTotalPaidForEvent();

        $this->assertEquals(30000, $totalPaid);
    }

    public function testGetOrdersTotalForEvent()
    {
        $now = new \DateTime();

        $this->orderVatViewsByEventQueryHandler
            ->handle(new OrderVatViewsByEventQuery($this->event))
            ->shouldBeCalled()
            ->willReturn(
                [
                    new OrderVatView('Order1', 1, 1, true, 20, 'ati', 'EUR', false, 200, 40, 240, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', false, 1000, 200, 1200, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', true, 2000, 400, 2400, $now),
                ]
            )
        ;

        $this->balance->loadAllOrderVatViews($this->event);
        $totalOrders = $this->balance->getOrdersTotalForEvent();

        $this->assertEquals(1440, $totalOrders);
    }

    public function testGetTotalRemainingToPayForEvent()
    {
        $now = new \DateTime();

        $this->orderVatViewsByEventQueryHandler
            ->handle(new OrderVatViewsByEventQuery($this->event))
            ->shouldBeCalled()
            ->willReturn(
                [
                    new OrderVatView('Order1', 1, 1, true, 20, 'ati', 'EUR', false, 2000, 400, 2400, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', false, 1000, 200, 1200, $now),
                    new OrderVatView('Order2', 1, 1, true, 20, 'ati', 'EUR', true, 2000, 400, 2400, $now),
                ]
            );

        $this->transactionRepository->findByEventAndEnabledSheets($this->event)->shouldBeCalled()->willReturn(
            [
                new Transaction($this->sheet, 10, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 20, $now, 'paypal', '', 'paid', 'EUR'),
                new Transaction($this->sheet, 35, $now, 'paypal', '', 'pending', 'EUR'),
            ]
        );

        $this->balance->loadAllForEvent($this->event);
        $remainingToPayForEvent = $this->balance->getTotalRemainingToPayForEvent();

        $this->assertEquals(600, $remainingToPayForEvent);
    }
}
