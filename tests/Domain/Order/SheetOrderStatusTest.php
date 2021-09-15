<?php

namespace Proximum\Vimeet\Tests\Domain\Order;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Order\SheetOrderStatus;
use Proximum\Vimeet\Domain\View\OrderVatView;

class SheetOrderStatusTest extends TestCase
{
    /** @var ObjectProphecy|Sheet */
    private $sheet;

    /** @var SheetOrderStatus */
    private $sheetOrderStatus;

    /** @var ObjectProphecy|Balance */
    private $balance;

    protected function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);
        $this->balance = $this->prophesize(Balance::class);
        $this->sheetOrderStatus = new SheetOrderStatus($this->balance->reveal());
    }

    public function testGetNoOrderStatus()
    {
        $this->balance->getNotCancelledOrderVatViews($this->sheet)->shouldBeCalled()->willReturn([]);
        $this->assertEquals(Sheet\Constant::NO_ORDER, $this->sheetOrderStatus->getStatus($this->sheet->reveal()));
    }

    public function testGetTotalOrderEqualZeroStatus()
    {
        $orderVatView = $this->prophesize(OrderVatView::class);
        $this->balance->getNotCancelledOrderVatViews($this->sheet->reveal())->shouldBeCalled()->willReturn(
            [$orderVatView->reveal()]
        );
        $this->balance->getTotalWithoutVat($this->sheet->reveal())->shouldBeCalled()->willReturn(0);
        $this->assertEquals(
            Sheet\Constant::ORDER_STATUS_TOTAL_ORDER_EQUAL_ZERO,
            $this->sheetOrderStatus->getStatus($this->sheet->reveal())
        );
    }

    public function testGetTotalOrderSuperiorZeroStatus()
    {
        $orderVatView = $this->prophesize(OrderVatView::class);
        $this->balance->getNotCancelledOrderVatViews($this->sheet->reveal())->shouldBeCalled()->willReturn(
            [$orderVatView->reveal()]
        );
        $this->balance->getTotalWithoutVat($this->sheet->reveal())->shouldBeCalled()->willReturn(99);
        $this->assertEquals(
            Sheet\Constant::ORDER_STATUS_TOTAL_ORDER_SUPERIOR_ZERO,
            $this->sheetOrderStatus->getStatus($this->sheet->reveal())
        );
    }
}
