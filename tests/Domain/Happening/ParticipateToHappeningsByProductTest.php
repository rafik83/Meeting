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
use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Application\Command\Happening\ParticipateHandler;
use Proximum\Vimeet\Domain\Happening\HappeningsNotOverlapped;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningsByProduct;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipateToHappeningsByProductTest extends TestCase
{
    public function testHandle()
    {
        $happeningAlreadyParticipated = $this->prophesize(Happening::class);
        $happeningNotParticipated = $this->prophesize(Happening::class);
        $happeningOverlapped1 = $this->prophesize(Happening::class);
        $happeningOverlapped2 = $this->prophesize(Happening::class);

        $product = $this->prophesize(Product::class);
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);

        $participant = $this->prophesize(Participant::class);
        $participant->getSheet()->willReturn($sheet->reveal());
        $participant->getUser()->willReturn($user->reveal());

        $otherParticipant = $this->prophesize(Participant::class);

        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningRepository
            ->findByProduct($product->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    $happeningAlreadyParticipated->reveal(),
                    $happeningNotParticipated->reveal(),
                    $happeningOverlapped1->reveal(),
                    $happeningOverlapped2->reveal(),
                ]
            )
        ;

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository
            ->getParticipantsForHappening($sheet->reveal(), $happeningAlreadyParticipated->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    $otherParticipant->reveal(),
                    $participant->reveal(),
                ]
            )
        ;
        $participantRepository
            ->getParticipantsForHappening($sheet->reveal(), $happeningNotParticipated->reveal())
            ->shouldBeCalled()
            ->willReturn([$otherParticipant->reveal()])
        ;

        $happeningsNotOverlapped = $this->prophesize(HappeningsNotOverlapped::class);
        $happeningsNotOverlapped
            ->getHappeningsNotOverlapped(
                [
                    $happeningAlreadyParticipated->reveal(),
                    $happeningNotParticipated->reveal(),
                    $happeningOverlapped1->reveal(),
                    $happeningOverlapped2->reveal(),
                ]
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $happeningAlreadyParticipated->reveal(),
                    $happeningNotParticipated->reveal(),
                ]
            )
        ;

        $participateHandler = $this->prophesize(ParticipateHandler::class);
        $participateHandler
            ->handle(
                new Participate(
                    $happeningNotParticipated->reveal(),
                    $sheet->reveal(),
                    $user->reveal(),
                    [
                        $otherParticipant->reveal(),
                        $participant->reveal(),
                    ],
                    null,
                    null,
                    false,
                    false
                )
            )
            ->shouldBeCalled()
        ;

        $participateToHappeningsByProduct = new ParticipateToHappeningsByProduct(
            $happeningRepository->reveal(),
            $happeningsNotOverlapped->reveal(),
            $participantRepository->reveal(),
            $participateHandler->reveal()
        );
        $participateToHappeningsByProduct->handle($product->reveal(), $participant->reveal());
    }
}
