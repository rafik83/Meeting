<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Group\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Group\Participant\UpdateUsersSheets;
use Proximum\Vimeet\Application\Command\Group\Participant\UpdateUsersSheetsHandler;
use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Group\Participant\UsersParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\UsersParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Group\Participant\UpdateUsersSheetsResultView;
use Proximum\Vimeet\Application\View\Group\Participant\UserParticipantView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\UserToParticipant;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class UpdateUsersSheetsHandlerTest extends TestCase
{
    public function testUpdateUsersSheets()
    {
        $participantRepositoryMock = $this->prophesize(ParticipantRepositoryInterface::class);
        $userRepositoryMock = $this->prophesize(UserRepositoryInterface::class);
        $meetingRepositoryMock = $this->prophesize(MeetingRepositoryInterface::class);
        $requestRepositoryMock = $this->prophesize(RequestRepositoryInterface::class);
        $usersParticipantViewQueryHandlerMock = $this->prophesize(UsersParticipantViewQueryHandler::class);
        $userToParticipantMock = $this->prophesize(UserToParticipant::class);
        $sheetInfoGuesserCacheMock = $this->prophesize(SheetInfoGuesserCache::class);

        $groupMock = $this->prophesize(Group::class);

        $sheetMock1 = $this->prophesize(Sheet::class);
        $sheetMock2 = $this->prophesize(Sheet::class);

        $userMock1 = $this->prophesize(User::class);
        $userMock2 = $this->prophesize(User::class);

        $participantMock = $this->prophesize(Participant::class);

        $userParticipantViews = [
            1 => new UserParticipantView(1, 'jean@bon.com', 'Jean Bon', [$sheetMock1->reveal()]),
            2 => new UserParticipantView(2, 'huguette@prairie.com', 'Huguette Prairie', [$sheetMock1->reveal()]),
        ];

        $usersParticipantViewQueryHandlerMock
            ->handle(new UsersParticipantViewQuery($groupMock->reveal()))
            ->willReturn($userParticipantViews);

        $updateUsersSheetsHandler = new UpdateUsersSheetsHandler(
            $participantRepositoryMock->reveal(),
            $userRepositoryMock->reveal(),
            $meetingRepositoryMock->reveal(),
            $requestRepositoryMock->reveal(),
            $usersParticipantViewQueryHandlerMock->reveal(),
            $userToParticipantMock->reveal(),
            $sheetInfoGuesserCacheMock->reveal()
        );

        $userRepositoryMock
            ->getByIdsIndexedById([1, 2])
            ->shouldBeCalled()
            ->willReturn([1 => $userMock1->reveal(), 2 => $userMock2->reveal()]);

        $userToParticipantMock->handle($sheetMock2->reveal(), $userMock1->reveal())->shouldBeCalled();
        $userToParticipantMock->handle($sheetMock2->reveal(), $userMock2->reveal())->shouldBeCalled();

        $participantRepositoryMock
            ->getParticipantForUserAndSheet($userMock2->reveal(), $sheetMock1->reveal())
            ->shouldBeCalled()
            ->willReturn($participantMock->reveal());

        $meetingRepositoryMock
            ->hasScheduledMeetingByParticipant($participantMock->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $sheetInfoGuesserCacheMock->guessSheetTitle($sheetMock1->reveal(), null)->shouldNotBeCalled();
        $sheetInfoGuesserCacheMock->guessSheetTitle($sheetMock2->reveal(), null)->shouldNotBeCalled();

        $participantRepositoryMock->delete($participantMock->reveal())->shouldBeCalled();

        $updateUsersSheets = new UpdateUsersSheets($groupMock->reveal(), $userParticipantViews);

        $updateUsersSheets->sheetsByUser = [
            1 => [$sheetMock1->reveal(), $sheetMock2->reveal()],
            2 => [$sheetMock2->reveal()],
        ];

        $this->assertEquals([], $updateUsersSheetsHandler->handle($updateUsersSheets));
    }

    public function testRemoveUserFromSheetWithParticipantHasMeeting()
    {
        $participantRepositoryMock = $this->prophesize(ParticipantRepositoryInterface::class);
        $userRepositoryMock = $this->prophesize(UserRepositoryInterface::class);
        $meetingRepositoryMock = $this->prophesize(MeetingRepositoryInterface::class);
        $requestRepositoryMock = $this->prophesize(RequestRepositoryInterface::class);
        $usersParticipantViewQueryHandlerMock = $this->prophesize(UsersParticipantViewQueryHandler::class);
        $userToParticipantMock = $this->prophesize(UserToParticipant::class);
        $sheetInfoGuesserCacheMock = $this->prophesize(SheetInfoGuesserCache::class);

        $groupMock = $this->prophesize(Group::class);

        $sheetMock1 = $this->prophesize(Sheet::class);
        $sheetMock2 = $this->prophesize(Sheet::class);

        $userMock = $this->prophesize(User::class);

        $participantMock = $this->prophesize(Participant::class);

        $userParticipantViews = [
            1 => new UserParticipantView(1, 'jean@bon.com', 'Jean Bon', [$sheetMock1->reveal(), $sheetMock2->reveal()]),
        ];

        $usersParticipantViewQueryHandlerMock
            ->handle(new UsersParticipantViewQuery($groupMock->reveal()))
            ->willReturn($userParticipantViews);

        $updateUsersSheetsHandler = new UpdateUsersSheetsHandler(
            $participantRepositoryMock->reveal(),
            $userRepositoryMock->reveal(),
            $meetingRepositoryMock->reveal(),
            $requestRepositoryMock->reveal(),
            $usersParticipantViewQueryHandlerMock->reveal(),
            $userToParticipantMock->reveal(),
            $sheetInfoGuesserCacheMock->reveal()
        );

        $userRepositoryMock
            ->getByIdsIndexedById([1])
            ->shouldBeCalled()
            ->willReturn([1 => $userMock->reveal()]);

        $participantRepositoryMock
            ->getParticipantForUserAndSheet($userMock->reveal(), $sheetMock2->reveal())
            ->shouldBeCalled()
            ->willReturn($participantMock->reveal());

        $requestRepositoryMock
            ->hasAssignedRequestByParticipant($participantMock->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $meetingRepositoryMock
            ->hasScheduledMeetingByParticipant($participantMock->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $sheetInfoGuesserCacheMock->guessSheetTitle($sheetMock1->reveal(), null)->shouldNotBeCalled();
        $sheetInfoGuesserCacheMock
            ->guessSheetTitle($sheetMock2->reveal(), null)
            ->shouldBeCalled()
            ->willReturn('Sheet title 2');

        $participantRepositoryMock->delete($participantMock->reveal())->shouldNotBeCalled();

        $updateUsersSheets = new UpdateUsersSheets($groupMock->reveal(), $userParticipantViews);

        $updateUsersSheets->sheetsByUser = [
            1 => [$sheetMock1->reveal()],
        ];

        $this->assertEquals(
            [UpdateUsersSheetsResultView::createHasMeetingOnSheet('Jean Bon', 'Sheet title 2')],
            $updateUsersSheetsHandler->handle($updateUsersSheets)
        );
    }

    public function testRemoveUserFromSheetWithParticipantHasMeetingRequest()
    {
        $participantRepositoryMock = $this->prophesize(ParticipantRepositoryInterface::class);
        $userRepositoryMock = $this->prophesize(UserRepositoryInterface::class);
        $meetingRepositoryMock = $this->prophesize(MeetingRepositoryInterface::class);
        $requestRepositoryMock = $this->prophesize(RequestRepositoryInterface::class);
        $usersParticipantViewQueryHandlerMock = $this->prophesize(UsersParticipantViewQueryHandler::class);
        $userToParticipantMock = $this->prophesize(UserToParticipant::class);
        $sheetInfoGuesserCacheMock = $this->prophesize(SheetInfoGuesserCache::class);

        $groupMock = $this->prophesize(Group::class);

        $sheetMock1 = $this->prophesize(Sheet::class);
        $sheetMock2 = $this->prophesize(Sheet::class);

        $userMock = $this->prophesize(User::class);

        $participantMock = $this->prophesize(Participant::class);

        $userParticipantViews = [
            1 => new UserParticipantView(1, 'jean@bon.com', 'Jean Bon', [$sheetMock1->reveal(), $sheetMock2->reveal()]),
        ];

        $usersParticipantViewQueryHandlerMock
            ->handle(new UsersParticipantViewQuery($groupMock->reveal()))
            ->willReturn($userParticipantViews);

        $updateUsersSheetsHandler = new UpdateUsersSheetsHandler(
            $participantRepositoryMock->reveal(),
            $userRepositoryMock->reveal(),
            $meetingRepositoryMock->reveal(),
            $requestRepositoryMock->reveal(),
            $usersParticipantViewQueryHandlerMock->reveal(),
            $userToParticipantMock->reveal(),
            $sheetInfoGuesserCacheMock->reveal()
        );

        $userRepositoryMock
            ->getByIdsIndexedById([1])
            ->shouldBeCalled()
            ->willReturn([1 => $userMock->reveal()]);

        $participantRepositoryMock
            ->getParticipantForUserAndSheet($userMock->reveal(), $sheetMock2->reveal())
            ->shouldBeCalled()
            ->willReturn($participantMock->reveal());

        $meetingRepositoryMock
            ->hasScheduledMeetingByParticipant($participantMock->reveal())
            ->shouldNotBeCalled();

        $requestRepositoryMock
            ->hasAssignedRequestByParticipant($participantMock->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $sheetInfoGuesserCacheMock->guessSheetTitle($sheetMock1->reveal(), null)->shouldNotBeCalled();
        $sheetInfoGuesserCacheMock
            ->guessSheetTitle($sheetMock2->reveal(), null)
            ->shouldBeCalled()
            ->willReturn('Sheet title 2');

        $participantRepositoryMock->delete($participantMock->reveal())->shouldNotBeCalled();

        $updateUsersSheets = new UpdateUsersSheets($groupMock->reveal(), $userParticipantViews);

        $updateUsersSheets->sheetsByUser = [
            1 => [$sheetMock1->reveal()],
        ];

        $this->assertEquals(
            [UpdateUsersSheetsResultView::createHasMeetingRequestOnSheet('Jean Bon', 'Sheet title 2')],
            $updateUsersSheetsHandler->handle($updateUsersSheets)
        );
    }

    public function testRemoveUserFromSheetButAtLeastOneParticipantOnSheetIsNeeded()
    {
        $participantRepositoryMock = $this->prophesize(ParticipantRepositoryInterface::class);
        $userRepositoryMock = $this->prophesize(UserRepositoryInterface::class);
        $meetingRepositoryMock = $this->prophesize(MeetingRepositoryInterface::class);
        $requestRepositoryMock = $this->prophesize(RequestRepositoryInterface::class);
        $usersParticipantViewQueryHandlerMock = $this->prophesize(UsersParticipantViewQueryHandler::class);
        $userToParticipantMock = $this->prophesize(UserToParticipant::class);
        $sheetInfoGuesserCacheMock = $this->prophesize(SheetInfoGuesserCache::class);

        $groupMock = $this->prophesize(Group::class);

        $sheetMock1 = $this->prophesize(Sheet::class);
        $sheetMock2 = $this->prophesize(Sheet::class);

        $userMock = $this->prophesize(User::class);

        $participantMock = $this->prophesize(Participant::class);

        $userParticipantViews = [
            1 => new UserParticipantView(1, 'jean@bon.com', 'Jean Bon', [$sheetMock1->reveal(), $sheetMock2->reveal()]),
        ];

        $usersParticipantViewQueryHandlerMock
            ->handle(new UsersParticipantViewQuery($groupMock->reveal()))
            ->willReturn($userParticipantViews);

        $updateUsersSheetsHandler = new UpdateUsersSheetsHandler(
            $participantRepositoryMock->reveal(),
            $userRepositoryMock->reveal(),
            $meetingRepositoryMock->reveal(),
            $requestRepositoryMock->reveal(),
            $usersParticipantViewQueryHandlerMock->reveal(),
            $userToParticipantMock->reveal(),
            $sheetInfoGuesserCacheMock->reveal()
        );

        $userRepositoryMock
            ->getByIdsIndexedById([1])
            ->shouldBeCalled()
            ->willReturn([1 => $userMock->reveal()]);

        $participantRepositoryMock
            ->getParticipantForUserAndSheet($userMock->reveal(), $sheetMock2->reveal())
            ->shouldBeCalled()
            ->willReturn($participantMock->reveal());

        $meetingRepositoryMock
            ->hasScheduledMeetingByParticipant($participantMock->reveal())
            ->shouldNotBeCalled();

        $sheetMock2->countParticipants()->shouldBeCalled()->willReturn(1);

        $sheetInfoGuesserCacheMock->guessSheetTitle($sheetMock1->reveal(), null)->shouldNotBeCalled();
        $sheetInfoGuesserCacheMock
            ->guessSheetTitle($sheetMock2->reveal(), null)
            ->shouldBeCalled()
            ->willReturn('Sheet title 2');

        $participantRepositoryMock->delete($participantMock->reveal())->shouldNotBeCalled();

        $updateUsersSheets = new UpdateUsersSheets($groupMock->reveal(), $userParticipantViews);

        $updateUsersSheets->sheetsByUser = [
            1 => [$sheetMock1->reveal()],
        ];

        $this->assertEquals(
            [UpdateUsersSheetsResultView::createSheetMustHaveAtLeastOneParticipant('Jean Bon', 'Sheet title 2')],
            $updateUsersSheetsHandler->handle($updateUsersSheets)
        );
    }
}
