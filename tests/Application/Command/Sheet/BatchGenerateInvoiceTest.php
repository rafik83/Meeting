<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Invoice\Create;
use Proximum\Vimeet\Application\Command\Invoice\CreateHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchGenerateInvoice;
use Proximum\Vimeet\Application\Command\Sheet\BatchGenerateInvoiceHandler;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\OrdersToInvoice;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchGenerateInvoiceTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $date        = new \DateTime();
        $admin       = new Admin('email@email.com', 'test', 'test', 'fr', 'test', 'test', 'ROLE_SUPER_ADMIN', $date);
        $type        = new Type($event);
        $user        = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet       = new Sheet($event, $type, [], $user, $date);
        $address     = new Address('test', 'test', 'test', 'test');
        $billingInfo = new Order\BillingInfo('test', 'test', 'test','test', '0', '0', 'test@test.com', 'test', $address, 'FR42');
        $order       = new Order($sheet, true, $billingInfo, 'test', $date);
        $prefix      = new Prefix('Vimeet', 'Vi');
        $invoice     = new Invoice($event, $sheet, $prefix, 'Vi', 2017, 1, 4200, 4704, 504, $date);
        $orderToInvoiceView = new OrdersToInvoiceView([$order], [], 4200, 504, 4704);
        
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $ordersToInvoiceView = $this->prophesize(OrdersToInvoice::class);
        $createHandler = $this->prophesize(CreateHandler::class);
        
        $sheetRepository->getSheetsById([1])->shouldBeCalled()->willReturn([$sheet]);
        
        $ordersToInvoiceView->getOrdersToInvoiceViewForSheet($sheet)->shouldBeCalled()->willReturn($orderToInvoiceView);
        
        $orderRepository->set($order)->shouldBeCalled();
    
        $create = new Create(
            $sheet,
            $orderToInvoiceView
        );
        
        $createHandler->handle($create)->shouldBeCalled()->willReturn($invoice);
        
        
        $command = new BatchGenerateInvoice([1], $admin);
        $handler = new BatchGenerateInvoiceHandler(
            $sheetRepository->reveal(),
            $ordersToInvoiceView->reveal(),
            $orderRepository->reveal(),
            $createHandler->reveal()
        );
        
        $result = $handler->handle($command);
    
        $this->assertEquals(1, $result->count);
    }
}
