<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\Package;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Package\ProductByParticipantCartGetter;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;

class ProductByParticipantCartGetterTest extends TestCase
{
    public function testGetFromCartWithSeveralProductsAndNoAssignedProduct()
    {
        $sheet = $this->prophesize(Sheet::class);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(42);
        $participant1->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(2000);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $cart->getParticipantRows()->shouldBeCalled()->willReturn([]);

        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $participant1->reveal(),
                $participant2->reveal(),
            ]
        );

        $productByParticipantGetter = new ProductByParticipantCartGetter();
        $result = $productByParticipantGetter->getFromCart($cart->reveal());

        $expectedResult = [42 => null, 2000 => null];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetFromCartWithOneProduct()
    {
        $product1 = $this->prophesize(Product::class);

        $sheet = $this->prophesize(Sheet::class);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(42);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(2000);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $cartRow = $this->prophesize(CartRow::class);
        $cartRow->getParticipants()->shouldBeCalled()->willReturn([$participant1->reveal()]);
        $cartRow->getProduct()->shouldBeCalled()->willReturn($product1->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $cart->getParticipantRows()->shouldBeCalled()->willReturn([$cartRow->reveal()]);

        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $participant1->reveal(),
                $participant2->reveal(),
            ]
        );

        $productByParticipantGetter = new ProductByParticipantCartGetter();
        $result = $productByParticipantGetter->getFromCart($cart->reveal());

        $expectedResult = [42 => $product1->reveal(), 2000 => null];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetFromCartWithSeveralProducts()
    {
        $product1 = $this->prophesize(Product::class);

        $sheet = $this->prophesize(Sheet::class);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(42);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(2000);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn(null);

        $cartRow = $this->prophesize(CartRow::class);
        $cartRow->getParticipants()->shouldBeCalled()->willReturn([$participant1->reveal()]);
        $cartRow->getProduct()->shouldBeCalled()->willReturn($product1->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $cart->getParticipantRows()->shouldBeCalled()->willReturn([$cartRow->reveal()]);

        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $participant1->reveal(),
                $participant2->reveal(),
            ]
        );

        $productByParticipantGetter = new ProductByParticipantCartGetter();
        $result = $productByParticipantGetter->getFromCart($cart->reveal());

        $expectedResult = [42 => $product1->reveal(), 2000 => null];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetFromCartWithAssignedProductToParticipant()
    {
        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);

        $sheet = $this->prophesize(Sheet::class);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(42);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(2000);
        $participant2->getParticipantProduct()->shouldBeCalled()->willReturn($product2->reveal());

        $cartRow = $this->prophesize(CartRow::class);
        $cartRow->getParticipants()->shouldBeCalled()->willReturn([$participant1->reveal()]);
        $cartRow->getProduct()->shouldBeCalled()->willReturn($product1->reveal());

        $cart = $this->prophesize(Cart::class);
        $cart->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $cart->getParticipantRows()->shouldBeCalled()->willReturn([$cartRow->reveal()]);

        $sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $participant1->reveal(),
                $participant2->reveal(),
            ]
        );

        $productByParticipantGetter = new ProductByParticipantCartGetter();
        $result = $productByParticipantGetter->getFromCart($cart->reveal());

        $expectedResult = [42 => $product1->reveal(), 2000 => $product2->reveal()];

        $this->assertEquals($expectedResult, $result);
    }
}
