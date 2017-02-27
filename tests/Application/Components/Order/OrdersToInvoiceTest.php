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
use Proximum\Vimeet\Application\Query\Order\SummaryQueryHandler;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class OrdersToInvoiceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInvoiceViewForSheet()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();
        $event->setVatModeToExclusiveOfTaxes();

        $type = new Type($event);
        $package = new Package($event, 'package', $datetime);
        $type->setPackage($package);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $orderBillingInfo = new Order\BillingInfo(
            'gender',
            'lastname',
            'firstname',
            'function',
            'phone',
            'mobile',
            'company',
            'email@email.com',
            new Address('street', 'zipcode', 'city', 'FR'),
            'vatNumber'
        );

        $plan        = Product::createPlan($event, 'plan', '', 99, 20, 100);
        $participant = Product::createParticipant($event, 'participant', 1789, 20);
        $option      = Product::createOption($event, 'option', '', 169, 50, 10, 20, true);

        $orderOne = new Order($sheet, true, $orderBillingInfo, '[]', $datetime->modify('-5 day'));
        $orderOne->addRow(new Order\Row($orderOne, 1, $plan));
        $orderOne->addRow(new Order\Row($orderOne, 2, $participant));
        $orderOne->addRow(new Order\Row($orderOne, 1, $option));
        $sheet->addOrder($orderOne);

        $orderTwo = new Order($sheet, true, $orderBillingInfo, '[]', $datetime->modify('-2 day'));
        $orderTwo->addRow(new Order\Row($orderTwo, -1, $participant));
        $orderTwo->addRow(new Order\Row($orderTwo, 3, $option));
        $sheet->addOrder($orderTwo);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet)->shouldBeCalled()->willReturn([$orderOne, $orderTwo]);

        $summaryQueryHandler = $this->prophesize(SummaryQueryHandler::class);

        $vatApplicable = $this->prophesize(VatApplicable::class);
        $vatApplicable->onSheet($sheet)->shouldBeCalled()->willReturn(true);

        $orderToInvoice = new OrdersToInvoice(
            $orderRepository->reveal(),
            new Merger(),
            $summaryQueryHandler->reveal(),
            $vatApplicable->reveal(),
            $this->prophesize(SerializerAdapterInterface::class)->reveal()
        );

        $ordersToInvoiceView = $orderToInvoice->getOrdersToInvoiceViewForSheet($sheet);

        $expectedOrdersToInvoiceView = new OrdersToInvoiceView([$orderOne, $orderTwo], null, 256400, 51280, 307680);

        $this->assertEquals($expectedOrdersToInvoiceView, $ordersToInvoiceView);
    }

    public function testGetInvoiceViewForSheetWithNoOrder()
    {
        $datetime = new \DateTime();
        $event = EventFactory::createEvent();
        $event->setVatModeToExclusiveOfTaxes();

        $type = new Type($event);
        $package = new Package($event, 'package', $datetime);
        $type->setPackage($package);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet)->shouldBeCalled()->willReturn([]);

        $summaryQueryHandler = $this->prophesize(SummaryQueryHandler::class);

        $vatApplicable = $this->prophesize(VatApplicable::class);

        $orderToInvoice = new OrdersToInvoice(
            $orderRepository->reveal(),
            new Merger(),
            $summaryQueryHandler->reveal(),
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
        $package = new Package($event, 'package', $datetime);
        $type->setPackage($package);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $orderBillingInfo = new Order\BillingInfo(
            'gender',
            'lastname',
            'firstname',
            'function',
            'phone',
            'mobile',
            'company',
            'email@email.com',
            new Address('street', 'zipcode', 'city', 'FR'),
            'vatNumber'
        );

        $option = Product::createOption($event, 'option', '', 169, 50, 10, 20, true);

        $orderNegative = new Order($sheet, true, $orderBillingInfo, '[]', $datetime->modify('-5 day'));
        $orderNegative->addRow(new Order\Row($orderNegative, -1, $option));
        $sheet->addOrder($orderNegative);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet)->shouldBeCalled()->willReturn([$orderNegative]);

        $summaryQueryHandler = $this->prophesize(SummaryQueryHandler::class);

        $vatApplicable = $this->prophesize(VatApplicable::class);

        $orderToInvoice = new OrdersToInvoice(
            $orderRepository->reveal(),
            new Merger(),
            $summaryQueryHandler->reveal(),
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
        $package = new Package($event, 'package', $datetime);
        $type->setPackage($package);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $orderBillingInfo = new Order\BillingInfo(
            'gender',
            'lastname',
            'firstname',
            'function',
            'phone',
            'mobile',
            'company',
            'email@email.com',
            new Address('street', 'zipcode', 'city', 'FR'),
            'vatNumber'
        );

        $plan        = Product::createPlan($event, 'plan', '', 99, 20, 100);
        $participant = Product::createParticipant($event, 'participant', 1789, 20);
        $option      = Product::createOption($event, 'option', '', 169, 50, 10, 20, true);
        $package->setPlans([$plan]);
        $package->setParticipant($participant);

        $orderOne = new Order($sheet, true, $orderBillingInfo, '[]', $datetime->modify('-5 day'));
        $orderOne->addRow(new Order\Row($orderOne, 1, $plan));
        $orderOne->addRow(new Order\Row($orderOne, 2, $participant));
        $orderOne->addRow(new Order\Row($orderOne, 1, $option));
        $sheet->addOrder($orderOne);

        $orderTwo = new Order($sheet, true, $orderBillingInfo, '[]', $datetime->modify('-2 day'));
        $orderTwo->addRow(new Order\Row($orderTwo, -1, $participant));

        $optionRow = new Order\Row($orderTwo, 3, $option);
        $orderTwo->addRow($optionRow);

        $package->setGroups([1 => [$option]], [1 => ['fr' => 'French label']]);

        $orderTwo->addRow(new Order\Row($orderTwo, 1, null, null, 'Remise', '-100', $optionRow));

        $sheet->addOrder($orderTwo);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet)->shouldBeCalled()->willReturn([$orderOne, $orderTwo]);

        $summaryQueryHandler = $this->prophesize(SummaryQueryHandler::class);

        $vatApplicable = $this->prophesize(VatApplicable::class);
        $vatApplicable->onSheet($sheet)->shouldBeCalled()->willReturn(true);

        $orderToInvoice = new OrdersToInvoice(
            $orderRepository->reveal(),
            new Merger(),
            $summaryQueryHandler->reveal(),
            $vatApplicable->reveal(),
            $this->prophesize(SerializerAdapterInterface::class)->reveal()
        );
        $ordersToInvoiceView = $orderToInvoice->getOrdersToInvoiceViewForSheet($sheet);

        $expectedOrdersToInvoiceView = new OrdersToInvoiceView([$orderOne, $orderTwo], null, 246400, 49280, 295680);

        $this->assertEquals($expectedOrdersToInvoiceView, $ordersToInvoiceView);
    }
}
