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
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Happening\UpdateParticipation;
use Proximum\Vimeet\Application\Command\Happening\UpdateParticipationHandler;
use Proximum\Vimeet\Domain\Happening\HappeningsNotOverlapped;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningsByProduct;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningWithProductToBuyChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ParticipantWithAttributedProductUpdated;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class ParticipateToHappeningsByProductTest extends TestCase
{
    private $event;
    private $sheet;
    private $participant1;
    private $participant2;
    private $participant3;
    private $happening1;
    private $happening2;
    private $happening3;
    private $happening4;

    private $product1;
    private $product2;
    private $product3;
    private $package;

    private $happeningRepository;
    private $happeningsNotOverlapped;
    private $participateToHappeningWithProductToBuyChecker;
    private $updateParticipationHandler;

    private $participateToHappeningsByProduct;
    private $participantWithAttributedProductUpdated;

    public function setUp()
    {
        $this->happening1 = $this->prophesize(Happening::class);
        $this->happening1->getId()->willReturn(901);

        $this->happening2 = $this->prophesize(Happening::class);
        $this->happening2->getId()->willReturn(902);

        $this->happening3 = $this->prophesize(Happening::class);
        $this->happening3->getId()->willReturn(903);

        $this->happening4 = $this->prophesize(Happening::class);
        $this->happening4->getId()->willReturn(904);

        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());

        $this->participant1 = $this->prophesize(Participant::class);
        $this->participant1->getId()->willReturn(111);

        $this->participant2 = $this->prophesize(Participant::class);
        $this->participant2->getId()->willReturn(222);

        $this->participant3 = $this->prophesize(Participant::class);
        $this->participant3->getId()->willReturn(333);

        $this->product1 = $this->prophesize(Product::class);
        $this->product1->getId()->willReturn(11);
        $this->product2 = $this->prophesize(Product::class);
        $this->product2->getId()->willReturn(22);
        $this->product3 = $this->prophesize(Product::class);
        $this->product3->getId()->willReturn(33);
        $this->package = $this->prophesize(Package::class);

        $this->sheet->getPackage()->willReturn($this->package->reveal());

        $this->participantWithAttributedProductUpdated = $this->prophesize(
            ParticipantWithAttributedProductUpdated::class
        );
        $this->happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $this->happeningsNotOverlapped = $this->prophesize(HappeningsNotOverlapped::class);
        $this->participateToHappeningWithProductToBuyChecker = $this->prophesize(
            ParticipateToHappeningWithProductToBuyChecker::class
        );
        $this->updateParticipationHandler = $this->prophesize(UpdateParticipationHandler::class);

        $this->participateToHappeningsByProduct = new ParticipateToHappeningsByProduct(
            $this->happeningRepository->reveal(),
            $this->happeningsNotOverlapped->reveal(),
            $this->participantWithAttributedProductUpdated->reveal(),
            $this->participateToHappeningWithProductToBuyChecker->reveal(),
            $this->updateParticipationHandler->reveal()
        );

    }

    public function testNoHappeningsWithProduct()
    {
        $this
            ->happeningRepository
            ->findWithProducts($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->updateParticipationHandler
            ->handle(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->participateToHappeningsByProduct->handle($this->sheet->reveal());
    }

    public function testHandle()
    {
        $this
            ->happeningRepository
            ->findWithProducts($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->happening1->reveal(),
                    $this->happening2->reveal(),
                    $this->happening3->reveal(),
                ]
            )
        ;

        $options = [
            11 => $this->product1->reveal(),
            22 => $this->product2->reveal(),
            33 => $this->product3->reveal(),
        ];
        $this->happening1->getProducts()->willReturn([$this->product1->reveal()]);
        $this->happening2->getProducts()->willReturn([$this->product1->reveal()]);
        $this->happening3->getProducts()->willReturn([$this->product2->reveal()]);
        $this->package->getAttributableOptions()->shouldBeCalled()->willReturn($options);

        $this->sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ]
        );

        $this
            ->participantWithAttributedProductUpdated
            ->getFilteredByParticipants([
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn([
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant1->reveal(),
                $this->happening1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant1->reveal(),
                $this->happening2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant1->reveal(),
                $this->happening3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->happeningsNotOverlapped
            ->getHappeningsNotOverlapped([$this->happening1->reveal(), $this->happening2->reveal()])
            ->shouldBeCalled()
            ->willReturn([$this->happening2->reveal()])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant2->reveal(),
                $this->happening1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant2->reveal(),
                $this->happening2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant2->reveal(),
                $this->happening3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->happeningsNotOverlapped
            ->getHappeningsNotOverlapped([$this->happening2->reveal(), $this->happening3->reveal()])
            ->shouldBeCalled()
            ->willReturn([$this->happening2->reveal(), $this->happening3->reveal()])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant3->reveal(),
                $this->happening1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant3->reveal(),
                $this->happening2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant3->reveal(),
                $this->happening3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->happeningsNotOverlapped
            ->getHappeningsNotOverlapped([$this->happening1->reveal(), $this->happening3->reveal()])
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->updateParticipationHandler
            ->handle(
                new UpdateParticipation(
                    $this->happening1->reveal(),
                    $this->sheet->reveal(),
                    []
                )
            )
            ->shouldBeCalled()
        ;

        $this
            ->updateParticipationHandler
            ->handle(
                new UpdateParticipation(
                    $this->happening2->reveal(),
                    $this->sheet->reveal(),
                    [
                        $this->participant1->reveal(),
                        $this->participant2->reveal(),
                    ]
                )
            )
            ->shouldBeCalled()
        ;

        $this
            ->updateParticipationHandler
            ->handle(
                new UpdateParticipation(
                    $this->happening3->reveal(),
                    $this->sheet->reveal(),
                    [
                        $this->participant2->reveal(),
                    ]
                )
            )
            ->shouldBeCalled()
        ;

        $this->participateToHappeningsByProduct->handle($this->sheet->reveal());
    }

    public function testHandleWithHappeningWithproductsNotInPackage()
    {
        $this
            ->happeningRepository
            ->findWithProducts($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->happening1->reveal(),
                    $this->happening2->reveal(),
                    $this->happening3->reveal(),
                    $this->happening4->reveal(),
                ]
            )
        ;

        $options = [
            11 => $this->product1->reveal(),
            22 => $this->product2->reveal(),
        ];
        $this->happening1->getProducts()->willReturn([$this->product1->reveal(), $this->product2->reveal()]);
        $this->happening2->getProducts()->willReturn([$this->product2->reveal()]);
        $this->happening3->getProducts()->willReturn([$this->product2->reveal()]);
        $this->happening4->getProducts()->willReturn([$this->product3->reveal()]);
        $this->package->getAttributableOptions()->shouldBeCalled()->willReturn($options);

        $this->sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ]
        );

        $this
            ->participantWithAttributedProductUpdated
            ->getFilteredByParticipants([
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn([
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant1->reveal(),
                $this->happening1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant1->reveal(),
                $this->happening2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant1->reveal(),
                $this->happening3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->happeningsNotOverlapped
            ->getHappeningsNotOverlapped([$this->happening1->reveal(), $this->happening2->reveal()])
            ->shouldBeCalled()
            ->willReturn([$this->happening2->reveal()])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant2->reveal(),
                $this->happening1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant2->reveal(),
                $this->happening2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant2->reveal(),
                $this->happening3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->happeningsNotOverlapped
            ->getHappeningsNotOverlapped([$this->happening2->reveal(), $this->happening3->reveal()])
            ->shouldBeCalled()
            ->willReturn([$this->happening2->reveal(), $this->happening3->reveal()])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant3->reveal(),
                $this->happening1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant3->reveal(),
                $this->happening2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant3->reveal(),
                $this->happening3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->happeningsNotOverlapped
            ->getHappeningsNotOverlapped([$this->happening1->reveal(), $this->happening3->reveal()])
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->updateParticipationHandler
            ->handle(
                new UpdateParticipation(
                    $this->happening1->reveal(),
                    $this->sheet->reveal(),
                    []
                )
            )
            ->shouldBeCalled()
        ;

        $this
            ->updateParticipationHandler
            ->handle(
                new UpdateParticipation(
                    $this->happening2->reveal(),
                    $this->sheet->reveal(),
                    [
                        $this->participant1->reveal(),
                        $this->participant2->reveal(),
                    ]
                )
            )
            ->shouldBeCalled()
        ;

        $this
            ->updateParticipationHandler
            ->handle(
                new UpdateParticipation(
                    $this->happening3->reveal(),
                    $this->sheet->reveal(),
                    [
                        $this->participant2->reveal(),
                    ]
                )
            )
            ->shouldBeCalled()
        ;

        $this->participateToHappeningsByProduct->handle($this->sheet->reveal());
    }

    public function testWithFilteredParticipants()
    {
        $this
            ->happeningRepository
            ->findWithProducts($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->happening1->reveal(),
                ]
            )
        ;

        $options = [
            11 => $this->product1->reveal(),
        ];
        $this->happening1->getProducts()->willReturn([$this->product2->reveal(), $this->product1->reveal()]);
        $this->package->getAttributableOptions()->shouldBeCalled()->willReturn($options);

        $this->sheet->getParticipantsArray()->shouldBeCalled()->willReturn(
            [
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ]
        );

        $this
            ->participantWithAttributedProductUpdated
            ->getFilteredByParticipants([
                $this->participant1->reveal(),
                $this->participant2->reveal(),
                $this->participant3->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn([
                $this->participant1->reveal(),
            ])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate(
                $this->participant1->reveal(),
                $this->happening1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->happeningsNotOverlapped
            ->getHappeningsNotOverlapped([])
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->updateParticipationHandler
            ->handle(
                new UpdateParticipation(
                    $this->happening1->reveal(),
                    $this->sheet->reveal(),
                    []
                )
            )
            ->shouldBeCalled()
        ;

        $this->participateToHappeningsByProduct->handle($this->sheet->reveal());
    }
}
