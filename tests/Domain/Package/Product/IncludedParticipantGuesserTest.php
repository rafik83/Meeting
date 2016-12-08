<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Package\Product;

use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\IncludedParticipantGuesser;
use Proximum\Vimeet\Domain\View\Package\Product\IncludedParticipantView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class IncludedParticipantGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function testNoPlanSelected()
    {
        $event       = EventFactory::createEvent();
        $sheet       = SheetFactory::create($event);
        $participant = ParticipantFactory::create($sheet);
        $sheet->addParticipant($participant);

        $cartManager = $this->prophesize(CartManager::class);
        $orderMerger = $this->prophesize(Merger::class);

        $includedParticipantGuesser = new IncludedParticipantGuesser($cartManager->reveal(), $orderMerger->reveal());

        $cart = new Cart($sheet, [], []);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart);

        $includedParticipantView = $includedParticipantGuesser->getIncludedParticipantView($sheet);

        $expectedIncludedParticipantView = new IncludedParticipantView(null, 0, 0);

        $this->assertEquals($expectedIncludedParticipantView, $includedParticipantView);
    }

    public function testPlanSelectedInCart()
    {
        $event       = EventFactory::createEvent();
        $sheet       = SheetFactory::create($event);
        $participant = ParticipantFactory::create($sheet);
        $sheet->addParticipant($participant);

        $cartManager = $this->prophesize(CartManager::class);
        $orderMerger = $this->prophesize(Merger::class);

        $includedParticipantGuesser = new IncludedParticipantGuesser($cartManager->reveal(), $orderMerger->reveal());

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
}
