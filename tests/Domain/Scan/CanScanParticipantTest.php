<?php

namespace Proximum\Vimeet\Tests\Domain\Scan;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Scan\CanScanParticipant;

class CanScanParticipantTest extends TestCase
{
    /** @var CanScanParticipant */
    private $canScanParticipant;

    /** @var ObjectProphecy|Merger */
    private $orderMerger;

    protected function setUp()
    {
        $this->orderMerger = $this->prophesize(Merger::class);
        $this->canScanParticipant = new CanScanParticipant($this->orderMerger->reveal());
    }

    public function test_sheet_type_can_scan_participant()
    {
        $type = $this->prophesize(Type::class);
        $type->canScanParticipant()->shouldBeCalled()->willReturn(true);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());

        $this->assertTrue($this->canScanParticipant->isSatisfiedBy($sheet->reveal()));
    }

    public function test_sheet_type_can_not_scan_participant_and_has_no_order()
    {
        $type = $this->prophesize(Type::class);
        $type->canScanParticipant()->shouldBeCalled()->willReturn(false);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());

        $this->orderMerger->getMergedOrders($sheet->reveal())->shouldBeCalled()->willReturn(null);

        $this->assertFalse($this->canScanParticipant->isSatisfiedBy($sheet->reveal()));
    }

    public function test_sheet_has_order_and_option_that_can_scan_participant()
    {
        $option1 = $this->prophesize(Product::class);
        $option1->canScanParticipant()->shouldBeCalled()->willReturn(false);

        $option2 = $this->prophesize(Product::class);
        $option2->canScanParticipant()->shouldBeCalled()->willReturn(true);

        $option3 = $this->prophesize(Product::class);
        $option3->canScanParticipant()->shouldNotBeCalled();

        $order = $this->prophesize(Order::class);
        $order->getOptions()->shouldBeCalled()->willReturn([$option1->reveal(), $option2->reveal(), $option3->reveal()]);

        $type = $this->prophesize(Type::class);
        $type->canScanParticipant()->shouldBeCalled()->willReturn(false);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());

        $this->orderMerger->getMergedOrders($sheet->reveal())->shouldBeCalled()->willReturn($order->reveal());

        $this->assertTrue($this->canScanParticipant->isSatisfiedBy($sheet->reveal()));
    }

    public function test_sheet_has_order_and_plan_with_included_product_that_can_scan_participant()
    {
        $option1 = $this->prophesize(Product::class);
        $option1->canScanParticipant()->shouldBeCalled()->willReturn(false);

        $includedOption1 = $this->prophesize(Product::class);
        $includedOption1->canScanParticipant()->shouldBeCalled()->willReturn(false);

        $includedOption2 = $this->prophesize(Product::class);
        $includedOption2->canScanParticipant()->shouldBeCalled()->willReturn(true);

        $productIncluded1 = $this->prophesize(Product\ProductIncluded::class);
        $productIncluded1->getIncluded()->shouldBeCalled()->willReturn($includedOption1->reveal());

        $productIncluded2 = $this->prophesize(Product\ProductIncluded::class);
        $productIncluded2->getIncluded()->shouldBeCalled()->willReturn($includedOption2->reveal());

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedOptionProduct()->shouldBeCalled()->willReturn(
            [$productIncluded1->reveal(), $productIncluded2->reveal()]
        );

        $order = $this->prophesize(Order::class);
        $order->getOptions()->shouldBeCalled()->willReturn([$option1->reveal()]);
        $order->getPlan()->shouldBeCalled()->willReturn($plan->reveal());

        $type = $this->prophesize(Type::class);
        $type->canScanParticipant()->shouldBeCalled()->willReturn(false);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());

        $this->orderMerger->getMergedOrders($sheet->reveal())->shouldBeCalled()->willReturn($order->reveal());

        $this->assertTrue($this->canScanParticipant->isSatisfiedBy($sheet->reveal()));
    }

    public function test_sheet_has_order_and_plan_with_included_product_that_can_not_scan_participant()
    {
        $option1 = $this->prophesize(Product::class);
        $option1->canScanParticipant()->shouldBeCalled()->willReturn(false);

        $includedOption1 = $this->prophesize(Product::class);
        $includedOption1->canScanParticipant()->shouldBeCalled()->willReturn(false);

        $includedOption2 = $this->prophesize(Product::class);
        $includedOption2->canScanParticipant()->shouldBeCalled()->willReturn(false);

        $productIncluded1 = $this->prophesize(Product\ProductIncluded::class);
        $productIncluded1->getIncluded()->shouldBeCalled()->willReturn($includedOption1->reveal());

        $productIncluded2 = $this->prophesize(Product\ProductIncluded::class);
        $productIncluded2->getIncluded()->shouldBeCalled()->willReturn($includedOption2->reveal());

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedOptionProduct()->shouldBeCalled()->willReturn(
            [$productIncluded1->reveal(), $productIncluded2->reveal()]
        );

        $order = $this->prophesize(Order::class);
        $order->getOptions()->shouldBeCalled()->willReturn([$option1->reveal()]);
        $order->getPlan()->shouldBeCalled()->willReturn($plan->reveal());

        $type = $this->prophesize(Type::class);
        $type->canScanParticipant()->shouldBeCalled()->willReturn(false);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());

        $this->orderMerger->getMergedOrders($sheet->reveal())->shouldBeCalled()->willReturn($order->reveal());

        $this->assertFalse($this->canScanParticipant->isSatisfiedBy($sheet->reveal()));
    }
}
