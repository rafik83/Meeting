<?php

namespace Proximum\Vimeet\Tests\Application\Components\Step;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Application\Components\Package\ProductByParticipantGetter;
use Proximum\Vimeet\Application\Components\Step\StepParticipantAndPlanning;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;

class StepParticipantAndPlanningTest extends TestCase
{
    /** @var ObjectProphecy */
    public $orderMerger;

    /** @var ObjectProphecy */
    public $cartManager;

    /** @var ObjectProphecy */
    public $productByParticipantGetter;

    /** @var ObjectProphecy */
    public $sheet;

    public function setUp()
    {
        $this->orderMerger = $this->prophesize(Merger::class);
        $this->cartManager = $this->prophesize(CartManager::class);
        $this->productByParticipantGetter = $this->prophesize(ProductByParticipantGetter::class);
        $this->sheet = $this->prophesize(Sheet::class);
    }

    public function testBuild()
    {
        $package = $this->prophesize(Package::class);
        $planning = $this->prophesize(Product::class);
        $planning->getType()->willReturn(Product::TYPE_PLANNING);
        $planning->getSerializedData()->willReturn('');
        $planning->getUnitPrice()->willReturn(123);
        $planning->isPlanning()->willReturn(true);
        $package->getPlanning()->willReturn($planning->reveal());
        $this->sheet->getPackage()->willReturn($package->reveal());
        $cartRowPlanning = new CartRow($this->sheet->reveal(), $planning->reveal(), 1);
        $cart = new Cart($this->sheet->reveal(), [$cartRowPlanning], [], null);

        $this->cartManager
            ->getCart($this->sheet->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($cart)
        ;

        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);
        $participantProducts = [
            123 => null,
            45 => $product1->reveal(),
            67 => $product2->reveal(),
        ];

        $this->productByParticipantGetter
            ->handle($cart)
            ->shouldBeCalled()
            ->willReturn($participantProducts)
        ;

        $orderMerged = $this->prophesize(Order::class);
        $this->orderMerger
            ->getMergedOrders($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn($orderMerged);
        $order = $this->prophesize(Order::class);
        $orderRow = new Order\Row($order->reveal(), 2, 20, $planning->reveal(), null, 'label', 123, null);
        $orderMerged->getRowForProduct($planning)->shouldBeCalled()->willReturn($orderRow);

        $stepParticipantAndPlanning = new StepParticipantAndPlanning(
            $this->orderMerger->reveal(),
            $this->cartManager->reveal(),
            $this->productByParticipantGetter->reveal()
        );

        $result = $stepParticipantAndPlanning->build($this->sheet->reveal());

        $expected = new SelectParticipantAndPlanning($this->sheet->reveal(), null);
        $expected->participantsProduct = $participantProducts;
        $expected->planningQuantity = new OptionRow(3);

        $this->assertEquals($expected, $result);
    }

    public function testBuildWithoutCart()
    {
        $package = $this->prophesize(Package::class);
        $planning = $this->prophesize(Product::class);
        $planning->getType()->willReturn(Product::TYPE_PLANNING);
        $planning->getSerializedData()->willReturn('');
        $planning->getUnitPrice()->willReturn(123);
        $planning->isPlanning()->willReturn(true);
        $package->getPlanning()->willReturn($planning->reveal());
        $this->sheet->getPackage()->willReturn($package->reveal());
        $cart = new Cart($this->sheet->reveal(), [], [], null);

        $this->cartManager
            ->getCart($this->sheet->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($cart)
        ;

        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);
        $participantProducts = [
            123 => null,
            45 => $product1->reveal(),
            67 => $product2->reveal(),
        ];

        $this->productByParticipantGetter
            ->handle($cart)
            ->shouldBeCalled()
            ->willReturn($participantProducts)
        ;

        $orderMerged = $this->prophesize(Order::class);
        $this->orderMerger
            ->getMergedOrders($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn($orderMerged);
        $order = $this->prophesize(Order::class);
        $orderRow = new Order\Row($order->reveal(), 2, 20, $planning->reveal(), null, 'label', 123, null);
        $orderMerged->getRowForProduct($planning)->shouldBeCalled()->willReturn($orderRow);

        $stepParticipantAndPlanning = new StepParticipantAndPlanning(
            $this->orderMerger->reveal(),
            $this->cartManager->reveal(),
            $this->productByParticipantGetter->reveal()
        );

        $result = $stepParticipantAndPlanning->build($this->sheet->reveal());

        $expected = new SelectParticipantAndPlanning($this->sheet->reveal(), null);
        $expected->participantsProduct = $participantProducts;
        $expected->planningQuantity = new OptionRow(2);

        $this->assertEquals($expected, $result);
    }

    public function testBuildWithoutOrder()
    {
        $package = $this->prophesize(Package::class);
        $planning = $this->prophesize(Product::class);
        $planning->getType()->willReturn(Product::TYPE_PLANNING);
        $planning->getSerializedData()->willReturn('');
        $planning->getUnitPrice()->willReturn(123);
        $planning->isPlanning()->willReturn(true);
        $package->getPlanning()->willReturn($planning->reveal());
        $this->sheet->getPackage()->willReturn($package->reveal());
        $cartRowPlanning = new CartRow($this->sheet->reveal(), $planning->reveal(), 1);
        $cart = new Cart($this->sheet->reveal(), [$cartRowPlanning], [], null);

        $this->cartManager
            ->getCart($this->sheet->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($cart)
        ;

        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);
        $participantProducts = [
            123 => null,
            45 => $product1->reveal(),
            67 => $product2->reveal(),
        ];

        $this->productByParticipantGetter
            ->handle($cart)
            ->shouldBeCalled()
            ->willReturn($participantProducts)
        ;

        $this->orderMerger
            ->getMergedOrders($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $stepParticipantAndPlanning = new StepParticipantAndPlanning(
            $this->orderMerger->reveal(),
            $this->cartManager->reveal(),
            $this->productByParticipantGetter->reveal()
        );

        $result = $stepParticipantAndPlanning->build($this->sheet->reveal());

        $expected = new SelectParticipantAndPlanning($this->sheet->reveal(), null);
        $expected->participantsProduct = $participantProducts;
        $expected->planningQuantity = new OptionRow(1);

        $this->assertEquals($expected, $result);
    }
}
