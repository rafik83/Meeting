<?php

namespace Proximum\Vimeet\Tests\Application\Query\Contact;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersView;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersViewQuery;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersViewQueryHandler;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GetContactListUsersViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);

        //    contact list participant
        $participantUser = $this->prophesize(User::class);
        $participantSheet = $this->prophesize(Sheet::class);

        $participant = $this->prophesize(Participant::class);
        $participant->getSheet()->shouldBeCalled()->willReturn($participantSheet->reveal());
        $participant->getUser()->shouldBeCalled()->willReturn($participantUser->reveal());
        $participantSheet
            ->getUserParticipant($participantUser->reveal())
            ->shouldBeCalled()
            ->willReturn($participant->reveal())
        ;

        //    requested users
        $requestedUser = $this->prophesize(User::class);

        $requestedParticipant = $this->prophesize(Participant::class);
        $requestedParticipant->getUser()->willReturn($requestedUser->reveal());

        $requestedUserSheet1 = $this->prophesize(Sheet::class);
        $requestedUserSheet1->getUserParticipant($requestedUser->reveal())
            ->willReturn($participant->reveal())
        ;

        $requestedUserSheet2 = $this->prophesize(Sheet::class);
        $requestedUserSheet2->getUserParticipant($requestedUser->reveal())
            ->willReturn($participant->reveal())
        ;
        $requestedUserSheet2->getParticipantsArray()->willReturn([$requestedParticipant->reveal()]);

        $request = $this->prophesize(Request::class);
        $request->hasNoPreference($participantSheet->reveal())->shouldBeCalled()->willReturn(false);
        $request
            ->getSheetMet($participantSheet->reveal())
            ->willReturn($requestedUserSheet2->reveal())
        ;

        //    scanned user
        $scannedUser = $this->prophesize(User::class);

        // prophesy dependencies
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $meetingParticipants = $this->prophesize(MeetingParticipants::class);
        $contactRepository = $this->prophesize(ContactRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $requestRepository->findApproved($participantSheet->reveal())
            ->willReturn([$request->reveal()])
        ;

        $meetingParticipants->getMeetingParticipants($request->reveal(), $participantSheet->reveal())
            ->willReturn([$participant->reveal()])
        ;
        $meetingParticipants->getMeetingParticipants($request->reveal(), $requestedUserSheet2->reveal())
            ->willReturn([$requestedParticipant->reveal()])
        ;

        $contactRepository->findSeenUserByEventAndUser($event->reveal(), $participantUser->reveal())
            ->willReturn([$requestedUser->reveal(), $scannedUser->reveal()])
        ;

        $sheetRepository
            ->getSheetsByUserAndEvent($participantUser->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$participantSheet->reveal()])
        ;

        $meeting1 = $this->prophesize(Meeting::class);
        $meeting2 = $this->prophesize(Meeting::class);

        $userMeeting1 = $this->prophesize(User::class);
        $userMeeting2 = $this->prophesize(User::class);
        $userMeeting3 = $this->prophesize(User::class);
        $participant1MetInMeeting1 = $this->prophesize(Participant::class);
        $participant2MetInMeeting1 = $this->prophesize(Participant::class);
        $participant1MetInMeeting2 = $this->prophesize(Participant::class);

        $meeting1->getMetParticipants($participantSheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant1MetInMeeting1->reveal(), $participant2MetInMeeting1->reveal()])
        ;

        $meeting2->getMetParticipants($participantSheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant1MetInMeeting2->reveal()])
        ;

        $participant1MetInMeeting1->getUser()->shouldBeCalled()->willReturn($userMeeting1->reveal());
        $participant2MetInMeeting1->getUser()->shouldBeCalled()->willReturn($userMeeting2->reveal());
        $participant1MetInMeeting2->getUser()->shouldBeCalled()->willReturn($userMeeting3->reveal());

        $meetingRepository
            ->getBySheets($event->reveal(), [$participantSheet->reveal()])
            ->shouldBeCalled()
            ->willReturn([$meeting1->reveal(), $meeting2->reveal()])
        ;

        // run tests
        $query = new GetContactListUsersViewQuery($event->reveal(), $participant->reveal());
        $handler = new GetContactListUsersViewQueryHandler(
            $meetingRepository->reveal(),
            $requestRepository->reveal(),
            $meetingParticipants->reveal(),
            $contactRepository->reveal(),
            $sheetRepository->reveal()
        );
        $result = $handler->handle($query);

        $expected = new GetContactListUsersView(
            [$requestedUser->reveal(), $scannedUser->reveal()],
            [$requestedUser->reveal()],
            [$userMeeting1->reveal(), $userMeeting2->reveal(), $userMeeting3->reveal()]
        );

        $this->assertEquals($expected, $result);
    }
}
