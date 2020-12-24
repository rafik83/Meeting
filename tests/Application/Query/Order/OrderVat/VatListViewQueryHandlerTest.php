<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\OrderVat;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Order\OrderVat\VatListViewQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\VatListViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Application\View\Package\Vat\VatView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;

class VatListViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $order = $this->prophesize(Order::class);
        $order->getVatMode()->shouldBeCalled()->willReturn(Event::VAT_MODE_ATI);
        $order->getTotalWithoutVat()->shouldBeCalled()->willReturn(750);

        $row1 = $this->prophesize(Order\Row::class);
        $row2 = $this->prophesize(Order\Row::class);
        $row3 = $this->prophesize(Order\Row::class);
        $row1->getVatRate()->willReturn(20);
        $row2->getVatRate()->willReturn(20);
        $row3->getVatRate()->willReturn(10);
        $row1->getPrice()->willReturn(100);
        $row2->getPrice()->willReturn(200);
        $row3->getPrice()->willReturn(300);
        $row1->getQuantity()->willReturn(1);
        $row2->getQuantity()->willReturn(2);
        $row3->getQuantity()->willReturn(1);
        $promotionCodeRow = $this->prophesize(Order\PromotionCode::class);
        $promotionCodeRow->getVatRate()->willReturn(10);
        $promotionCodeRow->getPrice()->willReturn(-50);

        $order->getRows()->willReturn([$row1->reveal(), $row2->reveal(), $row3->reveal()]);
        $order->getPromotionCodes()->willReturn([$promotionCodeRow->reveal()]);

        $vatViews = [
            'vat_20' => new VatView(20, Event::VAT_MODE_ATI, 50000, 10000),
            'vat_10' => new VatView(10, Event::VAT_MODE_ATI, 25000, 2500),
        ];
        $vatListView = new VatListView(75000, 87500.0, true, Event::VAT_MODE_ATI, $vatViews);

        $vatListViewQueryHandler = new VatListViewQueryHandler();
        $result = $vatListViewQueryHandler->handle(new VatListViewQuery($order->reveal(), true));

        $this->assertEquals($vatListView, $result);
    }

    public function testHandleWithoutVat(): void
    {
        $order = $this->prophesize(Order::class);
        $order->getVatMode()->shouldBeCalled()->willReturn(Event::VAT_MODE_ATI);
        $order->getTotalWithoutVat()->shouldBeCalled()->willReturn(750);
        $order->getRows()->shouldNotBeCalled();
        $order->getPromotionCodes()->shouldNotBeCalled();

        $vatViews = [];
        $vatListView = new VatListView(75000, 75000, false, Event::VAT_MODE_ATI, $vatViews);

        $vatListViewQueryHandler = new VatListViewQueryHandler();
        $result = $vatListViewQueryHandler->handle(new VatListViewQuery($order->reveal(), false));

        $this->assertEquals($vatListView, $result);
    }
}
