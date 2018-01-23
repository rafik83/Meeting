<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Package\Product;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\IncludedParticipantGuesser;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Domain\View\Package\Product\IncludedParticipantView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class IncludedParticipantGuesserTest extends TestCase
{
    /**
     * No plan selected
     */
    public function testNoPlanSelected()
    {
        $event       = EventFactory::createEvent();
        $sheet       = SheetFactory::create($event);
        ParticipantFactory::create($sheet);

        $cartManager = $this->prophesize(CartManager::class);
        $vatApplicable = $this->prophesize(VatApplicable::class);

        $orderMerger = new Merger($vatApplicable->reveal());

        $includedParticipantGuesser = new IncludedParticipantGuesser($cartManager->reveal(), $orderMerger);

        $cart = new Cart($sheet, [], []);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart);

        $includedParticipantView = $includedParticipantGuesser->getIncludedParticipantView($sheet);

        $expectedIncludedParticipantView = new IncludedParticipantView(null, 0, 0);

        $this->assertEquals($expectedIncludedParticipantView, $includedParticipantView);
    }

    /**
     * Plan selected in cart with 2 included and 1 remaining
     */
    public function testPlanSelectedInCart()
    {
        $event       = EventFactory::createEvent();
        $sheet       = SheetFactory::create($event);
        ParticipantFactory::create($sheet);

        $cartManager = $this->prophesize(CartManager::class);
        $vatApplicable = $this->prophesize(VatApplicable::class);

        $orderMerger = new Merger($vatApplicable->reveal());

        $includedParticipantGuesser = new IncludedParticipantGuesser($cartManager->reveal(), $orderMerger);

        $plan                       = Product::createPlan($event, 'My plan', '', 99, 0, 0);
        $participantIncludedProduct = Product::createParticipant($event, 'My participant product', 0, 2);
        $plan->includeProduct($participantIncludedProduct, 2);

        $planCartRow = new CartRow($sheet, $plan, 1);
        $cart        = new Cart($sheet, [$planCartRow], []);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart);

        $includedParticipantView = $includedParticipantGuesser->getIncludedParticipantView($sheet);

        $expectedIncludedParticipantView = new IncludedParticipantView($participantIncludedProduct, 2, 1);

        $this->assertEquals($expectedIncludedParticipantView, $includedParticipantView);
    }

    /**
     * Plan selected in order with 1 included and 0 remaining
     */
    public function testPlanSelectedInOrder()
    {
        $event       = EventFactory::createEvent();
        $sheet       = SheetFactory::create($event);
        ParticipantFactory::create($sheet);

        $cartManager = $this->prophesize(CartManager::class);
        $vatApplicable = $this->prophesize(VatApplicable::class);

        $orderMerger = new Merger($vatApplicable->reveal());

        $includedParticipantGuesser = new IncludedParticipantGuesser($cartManager->reveal(), $orderMerger);

        $plan                       = Product::createPlan($event, 'My plan', '', 99, 0, 0);
        $participantIncludedProduct = Product::createParticipant($event, 'My participant product', 0, 2);
        $plan->includeProduct($participantIncludedProduct, 1);

        $cartManager->getCart($sheet)->shouldNotBeCalled();

        $order = Order::createFromSheet($sheet, new \DateTime());
        $row   = new Order\Row($order, 1, $plan);
        $order->addRow($row);
        $sheet->addOrder($order);

        $includedParticipantView = $includedParticipantGuesser->getIncludedParticipantView($sheet);

        $expectedIncludedParticipantView = new IncludedParticipantView($participantIncludedProduct, 1, 0);

        $this->assertEquals($expectedIncludedParticipantView, $includedParticipantView);
    }
}
