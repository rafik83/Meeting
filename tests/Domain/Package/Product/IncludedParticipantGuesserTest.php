<?php

namespace Proximum\Vimeet\Tests\Domain\Package\Product;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\IncludedParticipantGuesser;
use Proximum\Vimeet\Domain\View\Package\Product\IncludedParticipantView;

class IncludedParticipantGuesserTest extends TestCase
{
    /** @var ObjectProphecy */
    private $cartManager;

    /** @var ObjectProphecy */
    private $orderMerger;

    /** @var ObjectProphecy */
    private $sheet;

    public function setUp()
    {
        $this->cartManager = $this->prophesize(CartManager::class);
        $this->orderMerger = $this->prophesize(Merger::class);
        $this->sheet = $this->prophesize(Sheet::class);
    }

    public function testGetIncludedParticipantViewsNoOrderNoCart()
    {
        $cart = new Cart($this->sheet->reveal(), [], [], null);
        $this->orderMerger->getMergedOrders($this->sheet->reveal())->shouldBeCalled()->willReturn(null);
        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart);

        $includedParticipantGuesser = new IncludedParticipantGuesser(
            $this->cartManager->reveal(),
            $this->orderMerger->reveal()
        );
        $result = $includedParticipantGuesser->getIncludedParticipantViews($this->sheet->reveal());
        $expected = [];

        $this->assertEquals($expected, $result);
    }

    public function testGetIncludedParticipantViewsNoOrder()
    {
        $plan = $this->prophesize(Product::class);
        $plan->getSerializedData()->willReturn('');
        $plan->isPlan()->willReturn(true);
        $productParticipant1 = $this->prophesize(Product::class);
        $productParticipant2 = $this->prophesize(Product::class);
        $productParticipant1->getId()->willReturn(123);
        $productParticipant2->getId()->willReturn(321);
        $productIncluded1 = $this->prophesize(Product\ProductIncluded::class);
        $productIncluded2 = $this->prophesize(Product\ProductIncluded::class);
        $productIncluded1->getQuantity()->willReturn(1);
        $productIncluded2->getQuantity()->willReturn(2);
        $productIncluded1->getIncluded()->willReturn($productParticipant1->reveal());
        $productIncluded2->getIncluded()->willReturn($productParticipant2->reveal());
        $plan->getIncludedParticipantProducts()
            ->shouldBeCalled()
            ->willReturn([$productIncluded1->reveal(), $productIncluded2->reveal()])
        ;
        $cartRowPlan = new CartRow($this->sheet->reveal(), $plan->reveal(), 1);
        $cart = new Cart($this->sheet->reveal(), [$cartRowPlan], [], null);
        $this->orderMerger->getMergedOrders($this->sheet->reveal())->shouldBeCalled()->willReturn(null);
        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart);

        $includedParticipantGuesser = new IncludedParticipantGuesser(
            $this->cartManager->reveal(),
            $this->orderMerger->reveal()
        );
        $result = $includedParticipantGuesser->getIncludedParticipantViews($this->sheet->reveal());
        $expected = [
            123 => new IncludedParticipantView(
                $productParticipant1->reveal(),
                1,
                0
            ),
            321 => new IncludedParticipantView(
                $productParticipant2->reveal(),
                2,
                0
            ),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetIncludedParticipantViews()
    {
        $plan = $this->prophesize(Product::class);
        $plan->getSerializedData()->willReturn('');
        $plan->isPlan()->willReturn(true);
        $productParticipant1 = $this->prophesize(Product::class);
        $productParticipant2 = $this->prophesize(Product::class);
        $productParticipant1->getId()->willReturn(123);
        $productParticipant2->getId()->willReturn(321);
        $productIncluded1 = $this->prophesize(Product\ProductIncluded::class);
        $productIncluded2 = $this->prophesize(Product\ProductIncluded::class);
        $productIncluded1->getQuantity()->willReturn(1);
        $productIncluded2->getQuantity()->willReturn(2);
        $productIncluded1->getIncluded()->willReturn($productParticipant1->reveal());
        $productIncluded2->getIncluded()->willReturn($productParticipant2->reveal());
        $plan->getIncludedParticipantProducts()
            ->shouldBeCalled()
            ->willReturn([$productIncluded1->reveal(), $productIncluded2->reveal()])
        ;
        $orderMerged = $this->prophesize(Order::class);
        $orderMerged->getPlan()->willReturn($plan->reveal());
        $this->orderMerger->getMergedOrders($this->sheet->reveal())->shouldBeCalled()->willReturn($orderMerged->reveal());
        $this->cartManager->getCart(Argument::any())->shouldNotBeCalled();

        $includedParticipantGuesser = new IncludedParticipantGuesser(
            $this->cartManager->reveal(),
            $this->orderMerger->reveal()
        );
        $result = $includedParticipantGuesser->getIncludedParticipantViews($this->sheet->reveal());
        $expected = [
            123 => new IncludedParticipantView(
                $productParticipant1->reveal(),
                1,
                0
            ),
            321 => new IncludedParticipantView(
                $productParticipant2->reveal(),
                2,
                0
            ),
        ];

        $this->assertEquals($expected, $result);
    }
}
