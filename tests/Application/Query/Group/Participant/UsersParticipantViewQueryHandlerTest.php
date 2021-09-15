<?php

namespace Proximum\Vimeet\Tests\Application\Query\Group\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Group\Participant\UsersParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\UsersParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Group\Participant\UserParticipantView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UsersParticipantViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $groupMock = $this->prophesize(Group::class);

        $userMock1 = $this->prophesize(User::class);
        $userMock2 = $this->prophesize(User::class);

        $participantMock1 = $this->prophesize(Participant::class);
        $participantMock2 = $this->prophesize(Participant::class);
        $participantMock3 = $this->prophesize(Participant::class);

        $sheetMock1 = $this->prophesize(Sheet::class);
        $sheetMock2 = $this->prophesize(Sheet::class);

        $participantRepositoryMock = $this->prophesize(ParticipantRepositoryInterface::class);

        $participantRepositoryMock->findByGroup($groupMock->reveal())->shouldBeCalled()->willReturn(
            [$participantMock1->reveal(), $participantMock2->reveal(), $participantMock3->reveal()]
        );

        $participantMock1->getUser()->shouldBeCalled()->willReturn($userMock1->reveal());
        $participantMock1->getSheet()->shouldBeCalled()->willReturn($sheetMock1->reveal());

        $participantMock2->getUser()->shouldBeCalled()->willReturn($userMock1->reveal());
        $participantMock2->getSheet()->shouldBeCalled()->willReturn($sheetMock2->reveal());

        $participantMock3->getUser()->shouldBeCalled()->willReturn($userMock2->reveal());
        $participantMock3->getSheet()->shouldBeCalled()->willReturn($sheetMock2->reveal());

        $userMock1->getId()->shouldBeCalled()->willReturn(1);
        $userMock1->getEmail()->shouldBeCalled()->willReturn('patrick.sebastien@example.net');
        $userMock1->getFullname()->shouldBeCalled()->willReturn('Patrick Sebastien');

        $userMock2->getId()->shouldBeCalled()->willReturn(2);
        $userMock2->getEmail()->shouldBeCalled()->willReturn('lilou.dallas@example.net');
        $userMock2->getFullname()->shouldBeCalled()->willReturn('Lilou Dallas');

        $usersParticipantViewQueryHandler = new UsersParticipantViewQueryHandler($participantRepositoryMock->reveal());

        $userParticipantViews = $usersParticipantViewQueryHandler->handle(
            new UsersParticipantViewQuery($groupMock->reveal())
        );

        $expectedUserParticipantViews = [
            1 => new UserParticipantView(
                1, 'patrick.sebastien@example.net', 'Patrick Sebastien', [$sheetMock1->reveal(), $sheetMock2->reveal()]
            ),
            2 => new UserParticipantView(
                2, 'lilou.dallas@example.net', 'Lilou Dallas', [$sheetMock2->reveal()]
            ),
        ];

        $this->assertEquals($expectedUserParticipantViews, $userParticipantViews);
    }
}
