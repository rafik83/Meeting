<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningWithProductToBuyChecker;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class ParticipateToHappeningWithProductToBuyCheckerTest extends TestCase
{
    private $package;
    private $sheet;
    private $participant;
    private $toHappening;
    private $productAttributedToParticipantRepository;
    private $participateToHappeningWithProductToBuyChecker;
    private $product1;
    private $product2;
    private $product3;

    public function setUp()
    {
        $this->product1 = $this->prophesize(Product::class);
        $this->product1->getId()->willReturn(1);

        $this->product2 = $this->prophesize(Product::class);
        $this->product2->getId()->willReturn(2);

        $this->product3 = $this->prophesize(Product::class);
        $this->product3->getId()->willReturn(3);

        $this->package = $this->prophesize(Package::class);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getPackage()->willReturn($this->package->reveal());

        $this->participant = $this->prophesize(Participant::class);
        $this->participant->getSheet()->willReturn($this->sheet->reveal());

        $this->toHappening = $this->prophesize(Happening::class);

        $this->productAttributedToParticipantRepository = $this->prophesize(
            ProductAttributedToParticipantRepositoryInterface::class
        );

        $this->participateToHappeningWithProductToBuyChecker = new ParticipateToHappeningWithProductToBuyChecker(
            $this->productAttributedToParticipantRepository->reveal()
        );
    }

    public function testHappeningHasNotProduct()
    {
        $this->toHappening->hasProducts()->shouldBeCalled()->willReturn(false);
        $this->assertTrue(
            $this->participateToHappeningWithProductToBuyChecker->canParticipate(
                $this->participant->reveal(),
                $this->toHappening->reveal()
            )
        );
    }

    public function testPackageIsNotPassable()
    {
        $this->toHappening->hasProducts()->shouldBeCalled()->willReturn(true);
        $this->package->isPassable()->shouldBeCalled()->willReturn(false);

        $this->assertTrue(
            $this->participateToHappeningWithProductToBuyChecker->canParticipate(
                $this->participant->reveal(),
                $this->toHappening->reveal()
            )
        );
    }

    public function testPackageHasNotAtLeastOneProductNeededByHappening()
    {
        $this->toHappening->hasProducts()->shouldBeCalled()->willReturn(true);
        $this->toHappening->getProducts()->shouldBeCalled()->willReturn(
            [
                $this->product1->reveal(),
                $this->product3->reveal(),
            ]
        )
        ;

        $this->package->isPassable()->shouldBeCalled()->willReturn(true);
        $this->package->getOptions()->shouldBeCalled()->willReturn([$this->product2->reveal()]);

        $this->assertTrue(
            $this->participateToHappeningWithProductToBuyChecker->canParticipate(
                $this->participant->reveal(),
                $this->toHappening->reveal()
            )
        );
    }

    private function happeningHasProductsAndPackageHasAtLeastOneRequiredProduct()
    {
        $this->toHappening->hasProducts()->shouldBeCalled()->willReturn(true);
        $this->toHappening->getProducts()->shouldBeCalled()->willReturn(
            [
                $this->product1->reveal(),
                $this->product3->reveal(),
            ]
        )
        ;

        $this->package->isPassable()->shouldBeCalled()->willReturn(true);
        $this->package->getOptions()->shouldBeCalled()->willReturn(
            [
                $this->product2->reveal(),
                $this->product3->reveal(),
            ]
        )
        ;
    }

    public function testParticipantHasNotRequiredProduct()
    {
        $this->happeningHasProductsAndPackageHasAtLeastOneRequiredProduct();

        $this
            ->productAttributedToParticipantRepository->participantHasAtLeastOneProduct(
                $this->participant,
                [
                    $this->product1->reveal(),
                    $this->product3->reveal(),
                ]
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->assertFalse(
            $this->participateToHappeningWithProductToBuyChecker->canParticipate(
                $this->participant->reveal(),
                $this->toHappening->reveal()
            )
        );
    }

    public function testParticipantHasRequiredProduct()
    {
        $this->happeningHasProductsAndPackageHasAtLeastOneRequiredProduct();

        $this
            ->productAttributedToParticipantRepository->participantHasAtLeastOneProduct(
                $this->participant,
                [
                    $this->product1->reveal(),
                    $this->product3->reveal(),
                ]
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->assertTrue(
            $this->participateToHappeningWithProductToBuyChecker->canParticipate(
                $this->participant->reveal(),
                $this->toHappening->reveal()
            )
        );
    }
}
