<?php

namespace Proximum\Vimeet\Tests\Application\Query\Contact;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Contact\ContactPreviewView;
use Proximum\Vimeet\Application\Query\Contact\GetContactListViewQuery;
use Proximum\Vimeet\Application\Query\Contact\GetContactListViewQueryHandler;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ContactListQueryHandlerTest extends TestCase
{
    public function test()
    {
        // prepare data
        $event = $this->prophesize(Event::class);

        $user = $this->prophesize(User::class);
        $user->getFullname()->willReturn('Carrie Fisher');
        $user->getId()->willReturn(42);

        $participantSheet = $this->prophesize(Sheet::class);

        $participant = $this->prophesize(Participant::class);
        $participant->getSheet()->willReturn($participantSheet->reveal());

        $metParticipant = $this->prophesize(Participant::class);
        $metParticipant->getUser()->willReturn($user->reveal());

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getUserParticipant($user->reveal())
            ->willReturn($participant->reveal())
        ;
        $sheet1->getTitle()->willReturn('New Republic');

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getUserParticipant($user->reveal())
            ->willReturn($participant->reveal())
        ;
        $sheet2->getTitle()->willReturn('Rebels');
        $sheet2->getParticipantsArray()->willReturn([$metParticipant->reveal()]);

        $request = $this->prophesize(Request::class);
        $request->getSheetMet($participantSheet->reveal())->willReturn()
            ->willReturn($sheet2->reveal())
        ;

        // prophecies dependencies
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $meetingParticipants = $this->prophesize(MeetingParticipants::class);

        $requestRepository->findApproved($participantSheet->reveal())
            ->willReturn([$request->reveal()])
        ;

        $sheetRepository->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet2->reveal(), $sheet1->reveal()])
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

        $meetingParticipants->getMeetingParticipants($request->reveal(), $participantSheet->reveal())
            ->willReturn([$participant->reveal()])
        ;

        // run tests
        $query = new GetContactListViewQuery($event->reveal(), $participant->reveal(), 'fr');
        $handler = new GetContactListViewQueryHandler(
            $sheetRepository->reveal(),
            $participantInfoGuesser->reveal(),
            $requestRepository->reveal(),
            $meetingParticipants->reveal()
        );
        $result = $handler->handle($query);

        $expected = [
            new ContactPreviewView(
                'Carrie', 'Fisher', 'http://far.away/leia.png', ['New Republic', 'Rebels'], true
            ),
        ];

        $this->assertEquals($expected, $result);
    }
}
