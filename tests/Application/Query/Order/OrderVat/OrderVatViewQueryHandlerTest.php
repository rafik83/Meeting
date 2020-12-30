<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\OrderVat;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\OrderVat\VatListViewQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\VatListViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Application\View\Package\Vat\VatView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Domain\View\OrderVatView;

class OrderVatViewQueryHandlerTest extends TestCase
{
    public function testHandleWithVat(): void
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

        $vatApplicable = $this->prophesize(VatApplicable::class);
        $vatApplicable->onSheet($sheet->reveal())->shouldBeCalled()->willReturn(true);

        $vatViews = [
            'vat_20' => new VatView(20, Event::VAT_MODE_ATI, 50000, 10000),
            'vat_10' => new VatView(10, Event::VAT_MODE_ATI, 25000, 2500),
        ];
        $vatListView = new VatListView(75000, 87500.0, true, Event::VAT_MODE_ATI, $vatViews);

        $vatListViewQueryHandler = $this->prophesize(VatListViewQueryHandler::class);
        $vatListViewQueryHandler->handle(new VatListViewQuery($order->reveal(), true))
            ->shouldBeCalled()
            ->willReturn($vatListView)
        ;

        $orderVatViewQueryHandler = new OrderVatViewQueryHandler(
            $vatApplicable->reveal(),
            $vatListViewQueryHandler->reveal()
        );
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
            75000,
            12500.0,
            87500.0,
            $vatListView,
            $now,
            null
        );

        $this->assertEquals($expectedOrderVatView, $orderVatView);
    }
}
