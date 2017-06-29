<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Cart;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRowRepositoryInterface;

class CartManagerTest extends TestCase
{
    private $cartRowRepository;
    private $cartStepRepository;
    private $promotionCodeRowRepository;
    private $orderMerger;

    public function setUp()
    {
        $this->cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);
        $this->cartStepRepository = $this->prophesize(CartStepRepositoryInterface::class);
        $this->promotionCodeRowRepository = $this->prophesize(PromotionCodeRowRepositoryInterface::class);
        $this->orderMerger = $this->prophesize(Merger::class);
    }

    public function testDeleteCartStep()
    {
        $cart = $this->prophesize(Cart::class);
        $sheet = $this->prophesize(Sheet::class);
        $cart->getSheet()->willReturn($sheet->reveal());

        // Mock
        $this->cartStepRepository->deleteForSheet($sheet->reveal())->shouldBeCalled();

        // CartManager
        $cartManager = new CartManager(
            $this->cartRowRepository->reveal(),
            $this->cartStepRepository->reveal(),
            $this->promotionCodeRowRepository->reveal(),
            $this->orderMerger->reveal()
        );

        $cartManager->deleteCartStep($cart->reveal());
    }

    public function testGetCart()
    {
        $cart = $this->prophesize(Cart::class);
        $sheet = $this->prophesize(Sheet::class);
        $cart->getSheet()->willReturn($sheet->reveal());
        $cartRow = $this->prophesize(CartRow::class);

        // Mock
        $this->cartRowRepository->findBySheet($sheet)->shouldBeCalled()->willReturn([$cartRow->reveal()]);
        $this->promotionCodeRowRepository->findBySheet($sheet)->shouldBeCalled()->willReturn([]);

        // CartManager
        $cartManager = new CartManager(
            $this->cartRowRepository->reveal(),
            $this->cartStepRepository->reveal(),
            $this->promotionCodeRowRepository->reveal(),
            $this->orderMerger->reveal()
        );

        $result = $cartManager->getCart($sheet->reveal(), 3);

        $expected = new Cart($sheet->reveal(), [$cartRow->reveal()], [], 3);

        $this->assertEquals($expected, $result);
    }
}
