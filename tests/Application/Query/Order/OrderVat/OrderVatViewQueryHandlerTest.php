<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Order\OrderVat;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Domain\View\OrderVatView;

class OrderVatViewQueryHandlerTest extends TestCase
{
    public function testHandleWithVat()
    {
        $now = new \DateTime();

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(34);

        $order = $this->prophesize(Order::class);
        $order->getId()->shouldBeCalled()->willReturn(109);
        $order->getSheet()->willReturn($sheet);
        $order->getNumero()->shouldBeCalled()->willReturn('1-34-109');
        $order->getVatMode()->shouldBeCalled()->willReturn(Event::VAT_MODE_ATI);
        $order->getVatRate()->shouldBeCalled()->willReturn(20);
        $order->getCurrency()->shouldBeCalled()->willReturn('EUR');
        $order->isCancelled()->shouldBeCalled()->willReturn(false);
        $order->getCreatedAt()->shouldBeCalled()->willReturn($now);
        $order->getInvoice()->shouldBeCalled()->willReturn(null);
        $order->getTotalWithoutVat()->shouldBeCalled()->willReturn(1000);

        $vatApplicable = $this->prophesize(VatApplicable::class);
        $vatApplicable->onSheet($sheet->reveal())->shouldBeCalled()->willReturn(true);

        $orderVatViewQueryHandler = new OrderVatViewQueryHandler($vatApplicable->reveal());
        $orderVatView = $orderVatViewQueryHandler->handle(new OrderVatViewQuery($order->reveal()));

        $expectedOrderVatView = new OrderVatView(
            '1-34-109',
            109,
            34,
            true,
            20,
            Event::VAT_MODE_ATI,
            'EUR',
            false,
            100000,
            20000,
            120000,
            $vatListView,
            $now,
            null
        );

        $this->assertEquals($expectedOrderVatView, $orderVatView);
    }

    public function testHandleWithoutVat()
    {
        $now = new \DateTime();

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(34);

        $order = $this->prophesize(Order::class);
        $order->getId()->shouldBeCalled()->willReturn(109);
        $order->getSheet()->willReturn($sheet);
        $order->getNumero()->shouldBeCalled()->willReturn('1-34-109');
        $order->getVatMode()->shouldBeCalled()->willReturn(Event::VAT_MODE_ATI);
        $order->getVatRate()->shouldBeCalled()->willReturn(20);
        $order->getCurrency()->shouldBeCalled()->willReturn('EUR');
        $order->isCancelled()->shouldBeCalled()->willReturn(false);
        $order->getCreatedAt()->shouldBeCalled()->willReturn($now);
        $order->getInvoice()->shouldBeCalled()->willReturn(null);
        $order->getTotalWithoutVat()->shouldBeCalled()->willReturn(1000);

        $vatApplicable = $this->prophesize(VatApplicable::class);
        $vatApplicable->onSheet($sheet->reveal())->shouldBeCalled()->willReturn(false);

        $orderVatViewQueryHandler = new OrderVatViewQueryHandler($vatApplicable->reveal());
        $orderVatView = $orderVatViewQueryHandler->handle(new OrderVatViewQuery($order->reveal()));

        $expectedOrderVatView = new OrderVatView(
            '1-34-109',
            109,
            34,
            false,
            20,
            Event::VAT_MODE_ATI,
            'EUR',
            false,
            100000,
            0,
            100000,
            $now,
            null
        );

        $this->assertEquals($expectedOrderVatView, $orderVatView);
    }
}
