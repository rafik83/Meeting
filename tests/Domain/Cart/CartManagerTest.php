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
use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Product\ProductIncluded;
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

    public function testUpdateParticipantsQuantityWithIncludedProduct()
    {
        $participantProduct1 = $this->prophesize(Product::class);
        $participantProduct1->getId()->shouldBeCalled()->willReturn(111);
        $participantProduct1->getSerializedData()->willReturn('participantProduct1');
        $participantProduct1->isParticipant()->willReturn(true);
        $participantProduct2 = $this->prophesize(Product::class);
        $participantProduct2->getId()->shouldBeCalled()->willReturn(222);

        $participantProductIncluded = $this->prophesize(ProductIncluded::class);
        $participantProductIncluded->getIncluded()->shouldBeCalled()->willReturn($participantProduct2->reveal());
        $participantProductIncluded->getQuantity()->shouldBeCalled()->willReturn(1);

        $order = $this->prophesize(Order::class);
        $order->getIncludedParticipantProducts()->shouldBeCalled()->willReturn([$participantProductIncluded->reveal()]);
        $order->getRowByProductId(111)->shouldBeCalled()->willReturn(null);
        $order->getRowByProductId(222)->shouldBeCalled()->willReturn(null);
        $order->getRowsProductOfParticipantType()->shouldBeCalled()->willReturn([]);

        $package = $this->prophesize(Package::class);
        $package
            ->getParticipants()
            ->shouldBeCalled()
            ->willReturn([$participantProduct1->reveal(), $participantProduct2->reveal()])
        ;

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(963);
        $participant1->setParticipantProduct()->shouldNotBeCalled();

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(1337);
        $participant2->setParticipantProduct($participantProduct2->reveal())->shouldBeCalled();

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->willReturn($package->reveal());
        $sheet
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn(
                [
                    $participant1->reveal(),
                    $participant2->reveal(),
                ]
            )
        ;

        $cart = new Cart(
            $sheet->reveal(),
            [],
            []
        );

        $this->orderMerger->getMergedOrders($sheet->reveal())->shouldBeCalled()->willReturn($order->reveal());

        // array of participantId => Product
        $productByParticipantId = [
            963 => $participantProduct1->reveal(),
            1337 => $participantProduct2->reveal(),
        ];

        $cartManager = new CartManager(
            $this->cartRowRepository->reveal(),
            $this->cartStepRepository->reveal(),
            $this->promotionCodeRowRepository->reveal(),
            $this->orderMerger->reveal()
        );

        $result = $cartManager->updateParticipantsQuantity($cart, $productByParticipantId);

        $expectedCartRow = new CartRow($sheet->reveal(), $participantProduct1->reveal(), 1);
        $expectedCartRow->addCartRowParticipant(new CartRowParticipant($expectedCartRow, $participant1->reveal()));
        $expected = new Cart($sheet->reveal(), [$expectedCartRow], []);

        $this->assertEquals($expected, $result);
    }
}
