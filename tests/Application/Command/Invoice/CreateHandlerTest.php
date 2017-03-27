<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Invoice;

use Proximum\Vimeet\Application\Command\Invoice\Create;
use Proximum\Vimeet\Application\Command\Invoice\CreateHandler;
use Proximum\Vimeet\Application\Components\Order\OrdersToInvoice;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event              = EventFactory::createEvent();
        $date               = new \DateTime();
        $type               = new Type($event);
        $user               = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet              = new Sheet($event, $type, [], $user, $date);
        $address            = new Address('test', 'test', 'test', 'test');
        //$billingInfo        = new BillingInfo('test', 'test', 'test', 'test', '0', '0', 'test@test.com', 'test', $address, 'FR42');
        $order              = new Order($sheet, true, 'test', $date);
        $prefix             = new Prefix('Vimeet', 'Vi');
        $invoice            = new Invoice($event, $sheet, $prefix, 'Vi', 2017, 1, true, 'et', 20, 1000, 1200, 200, 'EUR', '[]', $date);
        $orderToInvoiceView = new OrdersToInvoiceView([$order], '[]', true, 'et', 20, 1000, 200, 1200, 'EUR');

        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);
        $orderRepository   = $this->prophesize(OrderRepositoryInterface::class);
        $ordersToInvoice   = $this->prophesize(OrdersToInvoice::class);

        $ordersToInvoice->getOrdersToInvoiceViewForSheet($sheet)->shouldBeCalled()->willReturn($orderToInvoiceView);
        $invoiceRepository->getLastInvoiceForEventPrefix($prefix, $date->format('Y'))->shouldBeCalled();

        $invoiceRepository->add($invoice)->shouldBeCalled();

        $expectedOrder = clone $order;
        $expectedOrder->setInvoice($invoice);
        $orderRepository->set($expectedOrder)->shouldBeCalled();

        $handler = new CreateHandler(
            $invoiceRepository->reveal(),
            $orderRepository->reveal(),
            $ordersToInvoice->reveal(),
            $date
        );

        $handler->handle(new Create($sheet, $prefix, $orderToInvoiceView));
    }
}
