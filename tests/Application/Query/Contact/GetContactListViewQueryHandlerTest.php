<?php

namespace Proximum\Vimeet\Tests\Application\Query\Contact;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Contact\ContactListView;
use Proximum\Vimeet\Application\Query\Contact\ContactPreviewView;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersView;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersViewQuery;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersViewQueryHandler;
use Proximum\Vimeet\Application\Query\Contact\GetContactListViewQuery;
use Proximum\Vimeet\Application\Query\Contact\GetContactListViewQueryHandler;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetContactListViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);
        $event->accessControlEnabledAndShowCheckinStatus()->shouldBeCalled()->willReturn(true);

        //    contact list participant
        $participantSheet = $this->prophesize(Sheet::class);
        $participantUser = $this->prophesize(User::class);

        $participant = $this->prophesize(Participant::class);
        $participant->getSheet()->willReturn($participantSheet->reveal());
        $participant->getUser()->willReturn($participantUser->reveal());

        //    requested users
        $requestedUser = $this->prophesize(User::class);
        $requestedUser->getFullname()->willReturn('Carrie Fisher');
        $requestedUser->getId()->willReturn(42);

        $requestedParticipant = $this->prophesize(Participant::class);
        $requestedParticipant->getUser()->willReturn($requestedUser->reveal());

        $requestedUserSheet1 = $this->prophesize(Sheet::class);
        $requestedUserSheet1->getUserParticipant($requestedUser->reveal())
            ->willReturn($participant->reveal())
        ;
        $requestedUserSheet1->getTitle()->willReturn('New Republic');

        $requestedUserSheet2 = $this->prophesize(Sheet::class);
        $requestedUserSheet2->getUserParticipant($requestedUser->reveal())
            ->willReturn($participant->reveal())
        ;
        $requestedUserSheet2->getTitle()->willReturn('Rebels');
        $requestedUserSheet2->getParticipantsArray()->willReturn([$requestedParticipant->reveal()]);

        //    scanned user
        $scannedUser = $this->prophesize(User::class);
        $scannedUser->getFullname()->willReturn('Sam Fisher');
        $scannedUser->getId()->willReturn(314);

        $scannedParticipant = $this->prophesize(Participant::class);

        $scannedSheet = $this->prophesize(Sheet::class);
        $scannedSheet->getTitle()->willReturn('NSA');

        $scannedSheet->getUserParticipant($scannedUser->reveal())
            ->willReturn($scannedParticipant->reveal())
        ;

        // prophesy dependencies
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $getContactListUsersViewQueryHandler = $this->prophesize(GetContactListUsersViewQueryHandler::class);

        $sheetRepository->getSheetsByUserAndEvent($requestedUser->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$requestedUserSheet2->reveal(), $requestedUserSheet1->reveal()])
        ;

        $sheetRepository->getSheetsByUserAndEvent($scannedUser->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$scannedSheet->reveal()])
        ;

        $participantInfoGuesser->guessParticipantInfos($participant->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(
                [
                    'participant_firstname' => 'Carrie',
                    'participant_lastname'  => 'Fisher',
                    'participant_avatar'    => 'http://far.away/leia.png',
                ]
            )
        ;

        $participantInfoGuesser->guessParticipantInfos($scannedParticipant->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(
                [
                    'participant_firstname' => 'Sam',
                    'participant_lastname'  => 'Fisher',
                    'participant_avatar'    => 'http://nsa.org/sam.png',
                ]
            )
        ;

        $getContactListUsersViewQueryHandler->handle(
            new GetContactListUsersViewQuery($event->reveal(), $participant->reveal())
        )
            ->willReturn(new GetContactListUsersView([$scannedUser->reveal()], [$requestedUser->reveal()]))
        ;

        $dDayGuesser = $this->prophesize(DDayGuesser::class);
        $dDayGuesser->isItDDay($event->reveal())->shouldBeCalled()->willReturn(true);

        $now = new \DateTime('2020-02-05 17:01:01');

        $scanRepository = $this->prophesize(ScanRepositoryInterface::class);
        $scanRepository
            ->isUserCheckinTodayByEvent($requestedUser->reveal(), $event->reveal(), $now)
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $scanRepository
            ->isUserCheckinTodayByEvent($scannedUser->reveal(), $event->reveal(), $now)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        // run tests
        $query = new GetContactListViewQuery($event->reveal(), $participant->reveal(), 'fr');
        $handler = new GetContactListViewQueryHandler(
            $dDayGuesser->reveal(),
            $sheetRepository->reveal(),
            $participantInfoGuesser->reveal(),
            $getContactListUsersViewQueryHandler->reveal(),
            $scanRepository->reveal(),
            $now
        );
        $result = $handler->handle($query);

        $expected = new ContactListView(true, true, [
            new ContactPreviewView(
                42, 'Carrie', 'Fisher', 'http://far.away/leia.png', ['New Republic', 'Rebels'], true, true, false
            ),
            new ContactPreviewView(
                314, 'Sam', 'Fisher', 'http://nsa.org/sam.png', ['NSA'], false, false, true
            ),
        ]);

        $this->assertEquals($expected, $result);
    }
}
