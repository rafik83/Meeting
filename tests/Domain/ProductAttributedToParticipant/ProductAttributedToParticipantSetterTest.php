<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\ProductAttributedToParticipant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningsByProduct;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ProductAttributedToParticipantSetter;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class ProductAttributedToParticipantSetterTest extends TestCase
{
    private $participateToHappeningsByProduct;
    private $productAttributedToParticipantRepository;
    private $dateTime;
    private $productAttributedToParticipantSetter;
    private $participant1;
    private $participant2;
    private $product;
    private $productAttributedToParticipant1;

    public function setUp()
    {
        $this->product = $this->prophesize(Product::class);

        $this->participant1 = $this->prophesize(Participant::class);
        $this->participant1->getId()->willReturn(1);

        $this->participant2 = $this->prophesize(Participant::class);
        $this->participant2->getId()->willReturn(2);

        $this->productAttributedToParticipant1 = $this->prophesize(ProductAttributedToParticipant::class);
        $this->productAttributedToParticipant1->getParticipant()->willReturn($this->participant1->reveal());

        $this->productAttributedToParticipantRepository = $this->prophesize(
            ProductAttributedToParticipantRepositoryInterface::class
        );

        $this->participateToHappeningsByProduct = $this->prophesize(ParticipateToHappeningsByProduct::class);

        $this->dateTime = new \DateTime();

        $this->productAttributedToParticipantSetter = new ProductAttributedToParticipantSetter(
            $this->participateToHappeningsByProduct->reveal(),
            $this->productAttributedToParticipantRepository->reveal(),
            $this->dateTime
        );
    }

    public function testNoAddAndNoRemove()
    {
        $this
            ->productAttributedToParticipantRepository
            ->findByProductAndParticipants($this->product->reveal(), [$this->participant1->reveal()])
            ->willReturn([$this->productAttributedToParticipant1->reveal()])
        ;

        $this->productAttributedToParticipantRepository->add()->shouldNotBeCalled();
        $this->productAttributedToParticipantRepository->removeBatch()->shouldNotBeCalled();

        $this->productAttributedToParticipantSetter->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
            $this->product->reveal(),
            [$this->participant1->reveal()],
            [$this->participant1->reveal()]
        );
    }

    public function testAddAndNoRemove()
    {
        $this
            ->productAttributedToParticipantRepository
            ->findByProductAndParticipants($this->product->reveal(),
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                ]
            )
            ->willReturn([$this->productAttributedToParticipant1->reveal()])
        ;

        $this
            ->productAttributedToParticipantRepository
            ->add(
                new ProductAttributedToParticipant(
                    $this->product->reveal(),
                    $this->participant2->reveal(),
                    $this->dateTime
                )
            )
            ->shouldBeCalled()
        ;

        $this
            ->participateToHappeningsByProduct
            ->handle($this->product->reveal(), $this->participant2->reveal())
            ->shouldBeCalled()
        ;

        $this->productAttributedToParticipantRepository->removeBatch()->shouldNotBeCalled();

        $this->productAttributedToParticipantSetter->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
            $this->product->reveal(),
            [$this->participant1->reveal(), $this->participant2->reveal()],
            [$this->participant1->reveal(), $this->participant2->reveal()]
        );
    }

    public function testNoAddAndOneRemove()
    {
        $this
            ->productAttributedToParticipantRepository
            ->findByProductAndParticipants($this->product->reveal(), [$this->participant1->reveal()])
            ->willReturn([$this->productAttributedToParticipant1->reveal()])
        ;

        $this->productAttributedToParticipantRepository->add()->shouldNotBeCalled();

        $this
            ->productAttributedToParticipantRepository
            ->removeBatch([$this->productAttributedToParticipant1->reveal()])
            ->shouldBeCalled()
        ;

        $this->productAttributedToParticipantSetter->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
            $this->product->reveal(),
            [$this->participant1->reveal()],
            []
        );
    }

    public function testAddAndRemove()
    {
        $this
            ->productAttributedToParticipantRepository
            ->findByProductAndParticipants($this->product->reveal(),
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                ]
            )
            ->willReturn([$this->productAttributedToParticipant1->reveal()])
        ;

        $this
            ->productAttributedToParticipantRepository
            ->add(
                new ProductAttributedToParticipant(
                    $this->product->reveal(),
                    $this->participant2->reveal(),
                    $this->dateTime
                )
            )
            ->shouldBeCalled()
        ;

        $this
            ->participateToHappeningsByProduct
            ->handle($this->product->reveal(), $this->participant2->reveal())
            ->shouldBeCalled()
        ;

        $this
            ->productAttributedToParticipantRepository
            ->removeBatch([$this->productAttributedToParticipant1->reveal()])
            ->shouldBeCalled()
        ;

        $this->productAttributedToParticipantSetter->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
            $this->product->reveal(),
            [$this->participant1->reveal(), $this->participant2->reveal()],
            [$this->participant2->reveal()]
        );
    }
}
