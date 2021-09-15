<?php

namespace Proximum\Vimeet\Tests\Application\Query\Rooming\Stay;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Rooming\Stay\GetRoommates;
use Proximum\Vimeet\Application\Query\Rooming\Stay\GetRoommatesHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GetRoommatesHandlerTest extends TestCase
{
    private $sheetRepository;

    private $event;

    private $user1, $user2, $user3;

    private $sheet1, $sheet2;

    private $participant1, $participant2, $participant3;

    protected function setUp()
    {
        $this->user1 = $this->prophesize(User::class);

        $this->user2 = $this->prophesize(User::class);

        $this->user3 = $this->prophesize(User::class);

        $this->participant1 = $this->prophesize(Participant::class);

        $this->participant2 = $this->prophesize(Participant::class);

        $this->participant3 = $this->prophesize(Participant::class);

        $this->sheet1 = $this->prophesize(Sheet::class);

        $this->sheet2 = $this->prophesize(Sheet::class);

        $this->event = $this->prophesize(Event::class);

        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
    }

    public function testHandle(): void
    {
        $this->user1->getId()->shouldBeCalled()->willReturn(1);
        $this->user2->getId()->shouldBeCalled()->willReturn(2);
        $this->user3->getId()->shouldBeCalled()->willReturn(3);

        $this->participant1->getUser()->shouldBeCalled()->willReturn($this->user1->reveal());
        $this->participant2->getUser()->shouldBeCalled()->willReturn($this->user2->reveal());
        $this->participant3->getUser()->shouldBeCalled()->willReturn($this->user3->reveal());

        $this->sheet1
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                ]
            );
        $this->sheet2
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                    $this->participant3->reveal(),
                ]
            );

        $this->sheetRepository->getSheetsByUserAndEvent($this->user1->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->sheet1->reveal(),
                    $this->sheet2->reveal(),
                ]
            );

        $expectedResult = [
            2 => $this->user2->reveal(),
            3 => $this->user3->reveal(),
        ];

        $handler = new GetRoommatesHandler($this->sheetRepository->reveal());
        $result  = $handler->handle(new GetRoommates($this->user1->reveal(), $this->event->reveal(), null));

        $this->assertEquals($expectedResult, $result);
    }

    public function testOtherSheetHandle(): void
    {
        $this->user1->getId()->shouldBeCalled()->willReturn(1);
        $this->user2->getId()->shouldBeCalled()->willReturn(2);

        $this->participant1->getUser()->shouldBeCalled()->willReturn($this->user1->reveal());
        $this->participant2->getUser()->shouldBeCalled()->willReturn($this->user2->reveal());

        $this->sheet1
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                ]
            );

        $expectedResult = [
            2 => $this->user2->reveal(),
        ];

        $handler = new GetRoommatesHandler($this->sheetRepository->reveal());
        $result  = $handler->handle(new GetRoommates($this->user1->reveal(), $this->event->reveal(), $this->sheet1->reveal()));

        $this->assertEquals($expectedResult, $result);
    }
}
