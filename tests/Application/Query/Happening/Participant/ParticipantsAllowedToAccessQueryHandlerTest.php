<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Happening\Participant\ParticipantsAllowedToAccessQuery;
use Proximum\Vimeet\Application\Query\Happening\Participant\ParticipantsAllowedToAccessQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class ParticipantsAllowedToAccessQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $happening = $this->prophesize(Happening::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $user3 = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $happening->getEvent()->willReturn($event->reveal());
        $participant1->getUser()->willReturn($user1->reveal());
        $participant2->getUser()->willReturn($user2->reveal());
        $participant3->getUser()->willReturn($user3->reveal());

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet4 = $this->prophesize(Sheet::class);
        $sheet5 = $this->prophesize(Sheet::class);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type3 = $this->prophesize(Type::class);

        $sheet1->getType()->willReturn($type1->reveal());
        $sheet2->getType()->willReturn($type2->reveal());
        $sheet3->getType()->willReturn($type3->reveal());
        $sheet4->getType()->willReturn($type2->reveal());
        $sheet5->getType()->willReturn($type1->reveal());

        $happening->getTypes()->willReturn([$type1->reveal()]);

        // Mock
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $sheetRepository
            ->getSheetsByUserAndEvent($user1->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;
        $sheetRepository
            ->getSheetsByUserAndEvent($user2->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet3->reveal(), $sheet4->reveal()])
        ;
        $sheetRepository
            ->getSheetsByUserAndEvent($user3->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet5->reveal()])
        ;

        $query = new ParticipantsAllowedToAccessQuery(
            $happening->reveal(),
            [$participant1->reveal(), $participant2->reveal(), $participant3->reveal()]
        );

        $handler = new ParticipantsAllowedToAccessQueryHandler($sheetRepository->reveal());
        $result = $handler->handle($query);

        $expected = [
            $participant1->reveal(),
            $participant3->reveal(),
        ];

        $this->assertEquals($expected, $result);
    }
}
