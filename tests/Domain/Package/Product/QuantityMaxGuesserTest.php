<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Package\Product;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;

class QuantityMaxGuesserTest extends TestCase
{
    private $cartManager;
    private $merger;
    private $quantityMaxGuesser;
    private $sheet;
    private $package;

    public function setUp()
    {

        $this->package = $this->prophesize(Package::class);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getPackage()->willReturn($this->package->reveal());
        
        $this->cartManager = $this->prophesize(CartManager::class);
        $this->merger = $this->prophesize(Merger::class);

        $this->quantityMaxGuesser = new QuantityMaxGuesser($this->cartManager->reveal(), $this->merger->reveal());
    }

    public function testGetMaxPlanningWithSelectePlanAndIncludedPlanning()
    {
        $cart = $this->prophesize(Cart::class);

        $this->sheet->countParticipants()->shouldBeCalled()->willReturn(2);

        $planning = $this->prophesize(Product::class);
        $planning->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $planning->getAvailability()->shouldBeCalled()->willReturn(4);

        $this->package->getPlanning()->shouldBeCalled()->willReturn($planning->reveal());

        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        $includedPlanningProduct = $this->prophesize(Product\ProductIncluded::class);
        $includedPlanningProduct->getQuantity()->shouldBeCalled()->willReturn(1);

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedPlanningProduct()->shouldBeCalled()->willReturn($includedPlanningProduct->reveal());
        $cartRow = $this->prophesize(CartRow::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn($cartRow->reveal());
        $cartRow->getProduct()->shouldBeCalled()->willReturn($plan->reveal());

        $this->assertEquals(1, $this->quantityMaxGuesser->getMaxPlanning($this->sheet->reveal()));
    }

    public function testGetMaxPlanningWithoutIncludedPlanningLimitedByProductAvailability()
    {
        $cart = $this->prophesize(Cart::class);

        $this->sheet->countParticipants()->shouldBeCalled()->willReturn(4);

        $planning = $this->prophesize(Product::class);
        $planning->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $planning->getAvailability()->shouldBeCalled()->willReturn(2);

        $this->package->getPlanning()->shouldBeCalled()->willReturn($planning->reveal());

        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedPlanningProduct()->shouldBeCalled()->willReturn(null);
        $cartRow = $this->prophesize(CartRow::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn($cartRow->reveal());
        $cartRow->getProduct()->shouldBeCalled()->willReturn($plan->reveal());

        $this->assertEquals(2, $this->quantityMaxGuesser->getMaxPlanning($this->sheet->reveal()));
    }

    public function testGetMaxPlanningWithoutIncludedPlanningLimitedByProductQuantityMax()
    {
        $cart = $this->prophesize(Cart::class);

        $this->sheet->countParticipants()->shouldBeCalled()->willReturn(5);

        $planning = $this->prophesize(Product::class);
        $planning->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $planning->getAvailability()->shouldBeCalled()->willReturn(4);

        $this->package->getPlanning()->shouldBeCalled()->willReturn($planning->reveal());

        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedPlanningProduct()->shouldBeCalled()->willReturn(null);
        $cartRow = $this->prophesize(CartRow::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn($cartRow->reveal());
        $cartRow->getProduct()->shouldBeCalled()->willReturn($plan->reveal());

        $this->assertEquals(3, $this->quantityMaxGuesser->getMaxPlanning($this->sheet->reveal()));
    }

    public function testGetMaxPlanningWithoutIncludedPlanningLimitedByParticipantsNumber()
    {
        $cart = $this->prophesize(Cart::class);

        $this->sheet->countParticipants()->shouldBeCalled()->willReturn(1);

        $planning = $this->prophesize(Product::class);
        $planning->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $planning->getAvailability()->shouldBeCalled()->willReturn(4);

        $this->package->getPlanning()->shouldBeCalled()->willReturn($planning->reveal());

        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedPlanningProduct()->shouldBeCalled()->willReturn(null);
        $cartRow = $this->prophesize(CartRow::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn($cartRow->reveal());
        $cartRow->getProduct()->shouldBeCalled()->willReturn($plan->reveal());

        $this->assertEquals(1, $this->quantityMaxGuesser->getMaxPlanning($this->sheet->reveal()));
    }

    public function testThereIsNoCartNeitherOrder()
    {
        $cart = $this->prophesize(Cart::class);

        $this->sheet->countParticipants()->shouldBeCalled()->willReturn(1);
        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(false);

        $planning = $this->prophesize(Product::class);
        $planning->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $planning->getAvailability()->shouldBeCalled()->willReturn(4);

        $this->package->getPlanning()->shouldBeCalled()->willReturn($planning->reveal());

        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        $cart->getPlanRow()->shouldBeCalled()->willReturn(null);

        $this->assertEquals(1, $this->quantityMaxGuesser->getMaxPlanning($this->sheet->reveal()));
    }

    public function testGetMaxPlanningWithSelectePlanAndIncludedPlanningInOrder()
    {
        $cart = $this->prophesize(Cart::class);
        $cart->getPlanRow()->shouldBeCalled()->willReturn(null);

        $includedPlanningProduct = $this->prophesize(Product\ProductIncluded::class);
        $includedPlanningProduct->getQuantity()->shouldBeCalled()->willReturn(1);

        $plan = $this->prophesize(Product::class);
        $plan->getIncludedPlanningProduct()->shouldBeCalled()->willReturn($includedPlanningProduct->reveal());

        $mergedOrder = $this->prophesize(Order::class);
        $mergedOrder->getPlan()->shouldBeCalled()->willReturn($plan->reveal());

        $order = $this->prophesize(Order::class);

        $this->sheet->countParticipants()->shouldBeCalled()->willReturn(2);
        $this->sheet->hasNotCancelledOrders()->shouldBeCalled()->willReturn(true);
        $this->sheet->getNotCancelledOrders()->shouldBeCalled()->willReturn([$order->reveal()]);
        $this->merger->merge([$order->reveal()])->shouldBeCalled()->willReturn($mergedOrder->reveal());

        $planning = $this->prophesize(Product::class);
        $planning->getQuantityMax()->shouldBeCalled()->willReturn(3);
        $planning->getAvailability()->shouldBeCalled()->willReturn(4);

        $this->package->getPlanning()->shouldBeCalled()->willReturn($planning->reveal());

        $this->cartManager->getCart($this->sheet->reveal())->shouldBeCalled()->willReturn($cart->reveal());

        $this->assertEquals(1, $this->quantityMaxGuesser->getMaxPlanning($this->sheet->reveal()));
    }
}
