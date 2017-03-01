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
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Order\BillingInfo;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $date        = new \DateTime();
        $type        = new Type($event);
        $user        = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet       = new Sheet($event, $type, [], $user, $date);
        $address     = new Address('test', 'test', 'test', 'test');
        $billingInfo = new BillingInfo('test', 'test', 'test','test', '0', '0', 'test@test.com', 'test', $address, 'FR42');
        $order       = new Order($sheet, true, $billingInfo, 'test', $date);
        $prefix      = new Prefix('Vimeet', 'Vi');
        $invoice     = new Invoice($event, $sheet, $prefix, 'Vi', 2017, 1, 1000, 1200, 200, $date);
        $orderToInvoiceView = new OrdersToInvoiceView([$order], [], 1000, 200, 1200);
        
        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);
        
        $invoiceRepository->getLastInvoiceForEventPrefix($prefix, $date->format('Y'))->shouldBeCalled();
        
        $invoiceRepository->add($invoice)->shouldBeCalled()->willReturn($invoice);
        
        $create = new Create($sheet, $prefix, $orderToInvoiceView);
        
        $handler = new CreateHandler($invoiceRepository->reveal(), $date);
        
        $handler->handle($create);
    }
}
