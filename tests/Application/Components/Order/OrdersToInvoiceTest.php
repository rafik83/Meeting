<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\Order;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Components\Order\OrdersToInvoice;
use Proximum\Vimeet\Application\Query\Invoice\InvoiceDataQueryHandler;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class OrdersToInvoiceTest extends TestCase
{
    public function testGetInvoiceViewForSheet()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();
        $event->setVatModeToExclusiveOfTaxes();

        $type = new Type($event);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $plan        = Product::createPlan($event, 'plan', '', 99, 20, 100);
        $participant = Product::createParticipant($event, 'participant', 1789, 20);
        $option      = Product::createOption($event, 'option', '', 169, 50, 10, 20, true);

        $orderOne = new Order($sheet, '[]', $datetime->modify('-5 day'));
        $orderOne->addRow(new Order\Row($orderOne, 1, $plan));
        $orderOne->addRow(new Order\Row($orderOne, 2, $participant));
        $orderOne->addRow(new Order\Row($orderOne, 1, $option));
        $sheet->addOrder($orderOne);

        $orderTwo = new Order($sheet, '[]', $datetime->modify('-2 day'));
        $orderTwo->addRow(new Order\Row($orderTwo, -1, $participant));
        $orderTwo->addRow(new Order\Row($orderTwo, 3, $option));
        $sheet->addOrder($orderTwo);
        
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet)->shouldBeCalled()->willReturn([$orderOne, $orderTwo]);
        
        $invoiceDataQueryHandler = $this->prophesize(InvoiceDataQueryHandler::class);

        $vatApplicable = $this->prophesize(VatApplicable::class);
        $vatApplicable->onSheet($sheet)->shouldBeCalled()->willReturn(true);

        $orderToInvoice = new OrdersToInvoice(
            $orderRepository->reveal(),
            new Merger($vatApplicable->reveal()),
            $invoiceDataQueryHandler->reveal(),
            $vatApplicable->reveal(),
            $this->prophesize(SerializerAdapterInterface::class)->reveal()
        );

        $ordersToInvoiceView = $orderToInvoice->getOrdersToInvoiceViewForSheet($sheet);

        $expectedOrdersToInvoiceView = new OrdersToInvoiceView([$orderOne, $orderTwo], '', true, 'et', 20, 256400, 51280, 307680, 'EUR');

        $this->assertEquals($expectedOrdersToInvoiceView, $ordersToInvoiceView);
    }

    public function testGetInvoiceViewForSheetWithNoOrder()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();
        $event->setVatModeToExclusiveOfTaxes();

        $type = new Type($event);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet)->shouldBeCalled()->willReturn([]);
    
        $invoiceDataQueryHandler = $this->prophesize(InvoiceDataQueryHandler::class);

        $vatApplicable = $this->prophesize(VatApplicable::class);

        $orderToInvoice = new OrdersToInvoice(
            $orderRepository->reveal(),
            new Merger($vatApplicable->reveal()),
            $invoiceDataQueryHandler->reveal(),
            $vatApplicable->reveal(),
            $this->prophesize(SerializerAdapterInterface::class)->reveal()
        );

        $ordersToInvoiceView = $orderToInvoice->getOrdersToInvoiceViewForSheet($sheet);

        $this->assertNull($ordersToInvoiceView);
    }

    public function testGetInvoiceViewForSheetWithNegativeOrder()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();
        $event->setVatModeToExclusiveOfTaxes();

        $type = new Type($event);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $option = Product::createOption($event, 'option', '', 169, 50, 10, 20, true);

        $orderNegative = new Order($sheet, '[]', $datetime->modify('-5 day'));
        $orderNegative->addRow(new Order\Row($orderNegative, -1, $option));
        $sheet->addOrder($orderNegative);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet)->shouldBeCalled()->willReturn([$orderNegative]);
    
        $invoiceDataQueryHandler = $this->prophesize(InvoiceDataQueryHandler::class);

        $vatApplicable = $this->prophesize(VatApplicable::class);

        $orderToInvoice = new OrdersToInvoice(
            $orderRepository->reveal(),
            new Merger($vatApplicable->reveal()),
            $invoiceDataQueryHandler->reveal(),
            $vatApplicable->reveal(),
            $this->prophesize(SerializerAdapterInterface::class)->reveal()
        );

        $ordersToInvoiceView = $orderToInvoice->getOrdersToInvoiceViewForSheet($sheet);

        $this->assertNull($ordersToInvoiceView);
    }

    public function testGetInvoiceViewForSheetWithCustomRows()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();
        $event->setVatModeToExclusiveOfTaxes();

        $type = new Type($event);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $plan        = Product::createPlan($event, 'plan', '', 99, 20, 100);
        $participant = Product::createParticipant($event, 'participant', 1789, 20);
        $option      = Product::createOption($event, 'option', '', 169, 50, 10, 20, true);

        $groupId = 99;
        $groupsData = json_encode(
            [
                $groupId => [
                    'rank' => 1,
                    'translations' => [
                        'en' => ['label' => 'English label'],
                        'fr' => ['label' => 'French label'],
                    ]
                ]
            ]
        );

        $orderOne = new Order($sheet, $groupsData, $datetime->modify('-5 day'));
        $orderOne->addRow(new Order\Row($orderOne, 1, $plan));
        $orderOne->addRow(new Order\Row($orderOne, 2, $participant));
        $orderOne->addRow(new Order\Row($orderOne, 1, $option, $groupId));
        $sheet->addOrder($orderOne);

        $orderTwo = new Order($sheet, $groupsData, $datetime->modify('-2 day'));
        $orderTwo->addRow(new Order\Row($orderTwo, -1, $participant));

        $optionRow = new Order\Row($orderTwo, 3, $option, $groupId);
        $orderTwo->addRow($optionRow);

        // Add custom row attached to parent row
        $orderTwo->addRow(new Order\Row($orderTwo, 1, null, $groupId, 'Remise', -100, $optionRow));

        // Add custom row only attached to groupId
        $orderTwo->addRow(new Order\Row($orderTwo, 1, null, $groupId, 'Product XYZ', 239));

        $sheet->addOrder($orderTwo);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet)->shouldBeCalled()->willReturn([$orderOne, $orderTwo]);
    
        $invoiceDataQueryHandler = $this->prophesize(InvoiceDataQueryHandler::class);

        $vatApplicable = $this->prophesize(VatApplicable::class);
        $vatApplicable->onSheet($sheet)->shouldBeCalled()->willReturn(true);

        $orderToInvoice = new OrdersToInvoice(
            $orderRepository->reveal(),
            new Merger($vatApplicable->reveal()),
            $invoiceDataQueryHandler->reveal(),
            $vatApplicable->reveal(),
            $this->prophesize(SerializerAdapterInterface::class)->reveal()
        );
        $ordersToInvoiceView = $orderToInvoice->getOrdersToInvoiceViewForSheet($sheet);

        $expectedOrdersToInvoiceView = new OrdersToInvoiceView([$orderOne, $orderTwo], '', true, 'et', 20, 270300, 54060, 324360, 'EUR');

        $this->assertEquals($expectedOrdersToInvoiceView, $ordersToInvoiceView);
    }
}
