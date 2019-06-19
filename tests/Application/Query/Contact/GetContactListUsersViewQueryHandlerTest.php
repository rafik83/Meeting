<?php

namespace Proximum\Vimeet\Tests\Application\Query\Contact;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersView;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersViewQuery;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersViewQueryHandler;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class GetContactListUsersViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);

        //    contact list participant
        $participantSheet = $this->prophesize(Sheet::class);
        $participantUser = $this->prophesize(User::class);

        $participant = $this->prophesize(Participant::class);
        $participant->getSheet()->willReturn($participantSheet->reveal());
        $participant->getUser()->willReturn($participantUser->reveal());

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
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $meetingParticipants = $this->prophesize(MeetingParticipants::class);
        $contactRepository = $this->prophesize(ContactRepositoryInterface::class);

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

        // run tests
        $query = new GetContactListUsersViewQuery($event->reveal(), $participant->reveal());
        $handler = new GetContactListUsersViewQueryHandler(
            $requestRepository->reveal(),
            $meetingParticipants->reveal(),
            $contactRepository->reveal()
        );
        $result = $handler->handle($query);

        $expected = new GetContactListUsersView(
            [$requestedUser->reveal(), $scannedUser->reveal()],
            [$requestedUser->reveal()]
        );

        $this->assertEquals($expected, $result);
    }
}
