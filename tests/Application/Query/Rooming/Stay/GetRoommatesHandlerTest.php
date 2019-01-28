<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Rooming\Stay;

use Proximum\Vimeet\Application\Query\Rooming\Stay\GetRoommates;
use Proximum\Vimeet\Application\Query\Rooming\Stay\GetRoommatesHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GetRoommatesHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $user1 = $this->prophesize(User::class);
        $user1->getId()->shouldBeCalled()->willReturn(1);

        $user2 = $this->prophesize(User::class);
        $user2->getId()->shouldBeCalled()->willReturn(2);

        $user3 = $this->prophesize(User::class);
        $user3->getId()->shouldBeCalled()->willReturn(3);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getUser()->shouldBeCalled()->willReturn($user1->reveal());

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getUser()->shouldBeCalled()->willReturn($user2->reveal());

        $participant3 = $this->prophesize(Participant::class);
        $participant3->getUser()->shouldBeCalled()->willReturn($user3->reveal());

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn([
                $participant1->reveal(),
                $participant2->reveal(),
            ]);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn([
                $participant1->reveal(),
                $participant2->reveal(),
                $participant3->reveal()
            ]);

        $event = $this->prophesize(Event::class);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->getSheetsByUserAndEvent($user1->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([
                $sheet1->reveal(),
                $sheet2->reveal()
            ]);

        $expectedResult = [
            2 => $user2->reveal(),
            3 => $user3->reveal(),
        ];

        $handler = new GetRoommatesHandler($sheetRepository->reveal());
        $result = $handler->handle(new GetRoommates($user1->reveal(), $event->reveal(), null));

        $this->assertEquals($expectedResult, $result);
    }
}
