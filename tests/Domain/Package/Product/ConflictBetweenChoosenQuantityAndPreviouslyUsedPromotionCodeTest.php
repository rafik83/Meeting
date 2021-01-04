<?php

namespace Proximum\Vimeet\Tests\Domain\Package\Product;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode;

class ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCodeTest extends TestCase
{
    private $merger;
    private $sheet;
    private $product;
    private $orderRow;
    private $order;
    private $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode;

    public function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);
        $this->product = $this->prophesize(Product::class);
        $this->orderRow = $this->prophesize(Order\Row::class);
        $this->order = $this->prophesize(Order::class);
        $this->merger = $this->prophesize(Merger::class);

        $this->conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode = new ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode(
            $this->merger->reveal()
        );
    }

    private function previousOrderedQuantityAssignedToPromotionCodeIs(int $quantity)
    {
        $this->orderRow->getQuantity()->shouldBeCalled()->willReturn($quantity);
        $this->order->hasPromotionCodeForProduct($this->product->reveal())->shouldBeCalled()->willReturn(true);
        $this->order->getRowForProduct($this->product->reveal())->shouldBeCalled()->willReturn(
            $this->orderRow->reveal()
        );
    }

    public function testHasConflict()
    {
        $this->previousOrderedQuantityAssignedToPromotionCodeIs(2);

        $this->assertTrue(
            $this->conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->hasConflict(
                $this->sheet->reveal(),
                $this->product->reveal(),
                1,
                $this->order->reveal()
            )
        );
    }

    public function testHasNotConflict()
    {
        $this->previousOrderedQuantityAssignedToPromotionCodeIs(2);

        $this->assertFalse(
            $this->conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->hasConflict(
                $this->sheet->reveal(),
                $this->product->reveal(),
                2,
                $this->order->reveal()
            )
        );
    }

    public function testGetOrders()
    {
        $this->sheet->hasNotCancelledOrders()->willReturn(true);
        $this->sheet->getNotCancelledOrders()->willReturn([$this->order->reveal()]);
        $this->merger->merge([$this->order->reveal()])->willReturn($this->order->reveal());
        $this->previousOrderedQuantityAssignedToPromotionCodeIs(2);

        $this->assertFalse(
            $this->conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->hasConflict(
                $this->sheet->reveal(),
                $this->product->reveal(),
                2
            )
        );
    }

    public function testNoOrders()
    {
        $this->sheet->hasNotCancelledOrders()->willReturn(false);

        $this->assertFalse(
            $this->conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->hasConflict(
                $this->sheet->reveal(),
                $this->product->reveal(),
                2
            )
        );
    }

    public function testNoPromotionCodeAssignedToProduct()
    {
        $this->order->hasPromotionCodeForProduct($this->product->reveal())->shouldBeCalled()->willReturn(false);

        $this->assertFalse(
            $this->conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->hasConflict(
                $this->sheet->reveal(),
                $this->product->reveal(),
                2,
                $this->order->reveal()
            )
        );
    }
}
