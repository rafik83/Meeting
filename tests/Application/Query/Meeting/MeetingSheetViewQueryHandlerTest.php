<?php

namespace Proximum\Vimeet\Tests\Application\Query\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Meeting\MeetingSheetViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\MeetingSheetViewQueryHandler;
use Proximum\Vimeet\Application\Query\Meeting\ParticipantsViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\ParticipantsViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\MeetingParticipantView;
use Proximum\Vimeet\Application\View\Meeting\MeetingSheetListView;
use Proximum\Vimeet\Application\View\Meeting\MeetingSheetView;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class MeetingSheetViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy|RequestRepositoryInterface */
    private $requestRepository;

    /** @var ObjectProphecy|ParticipantsViewQueryHandler */
    private $participantsViewQueryHandler;

    /** @var ObjectProphecy|SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var ObjectProphecy|ContactRepositoryInterface */
    private $contactRepository;

    /** @var ObjectProphecy|SheetRepositoryInterface */
    private $sheetRepository;

    /** @var MeetingSheetViewQueryHandler */
    private $meetingSheetViewQueryHandler;

    public function setUp()
    {
        $this->contactRepository = $this->prophesize(ContactRepositoryInterface::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->participantsViewQueryHandler = $this->prophesize(ParticipantsViewQueryHandler::class);
        $this->sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);

        $this->meetingSheetViewQueryHandler = new MeetingSheetViewQueryHandler(
            $this->contactRepository->reveal(),
            $this->requestRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->participantsViewQueryHandler->reveal(),
            $this->sheetInfoGuesser->reveal()
        );
    }

    public function testHandle()
    {
        $user = new User('me@example.com', 'salt', 'pwd', 'fr');

        $event = $this->prophesize(Event::class);
        $event->getTitle()->shouldBeCalled()->willReturn('B2B Event');

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getUsers()->shouldBeCalled()->willReturn([$user]);

        $type = $this->prophesize(Type::class);
        $type->getTitle('fr')->shouldBeCalled()->willReturn('Exposant');

        $sheetMet1 = $this->prophesize(Sheet::class);
        $sheetMet1->getId()->shouldBeCalled()->willReturn(42);
        $sheetMet1->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheetMet1->getTitle()->shouldBeCalled()->willReturn('Sheet 1');

        $sheetMet1Participant = $this->prophesize(Participant::class);
        $sheetMet1->getFirstParticipant()->shouldBeCalled()->willReturn($sheetMet1Participant->reveal());

        $contact1 = new User('contact1@example.com', 'salt', 'pwd', 'fr');
        $contact2 = new User('contact2@example.com', 'salt', 'pwd', 'fr');
        $contact3 = new User('contact3@example.com', 'salt', 'pwd', 'fr');

        $contactScanned1 = $this->prophesize(Contact::class);
        $contactScanned2 = $this->prophesize(Contact::class);
        $contactScanned3 = $this->prophesize(Contact::class);

        $contactScanned1->getContact()->shouldBeCalled()->willReturn($contact1);
        $contactScanned2->getContact()->shouldBeCalled()->willReturn($contact2);
        $contactScanned3->getContact()->shouldBeCalled()->willReturn($contact3);

        $this->sheetInfoGuesser
            ->guessSheetInfos($sheetMet1->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(
                [
                    Tag::SHEET_TITLE => 'Sheet 1',
                    Tag::SHEET_ORGANIZATION_CATEGORY => 'Group',
                    Tag::SHEET_ORGANIZATION_STAFF => '100',
                    Tag::SHEET_ORGANIZATION_TURNOVER => '10M',
                    Tag::SHEET_WEBSITE => 'https://website.com',
                    Tag::SHEET_ADDRESS => '10 rue Saint-Marc',
                    Tag::SHEET_ZIPCODE => '75002',
                    Tag::SHEET_CITY => 'Paris',
                    Tag::SHEET_COUNTRY => 'FR',
                ]
            )
        ;
        $sheetMet1ParticipantView = new MeetingParticipantView(
            'Korben',
            'Dallas',
            'Taxi driver',
            '+33404',
            'man',
            'korben@taxi.space',
            1,
            'Violent guy'
        );
        $this->participantsViewQueryHandler
            ->handle(
                new ParticipantsViewQuery(
                    [$sheetMet1Participant->reveal()],
                    'fr',
                    [$contactScanned1->reveal(), $contactScanned2->reveal(), $contactScanned3->reveal()],
                    $sheet->reveal(),
                    $user
                )
            )
            ->shouldBeCalled()
            ->willReturn([$sheetMet1ParticipantView])
        ;

        $meetingRequest1 = $this->prophesize(Request::class);
        $meetingRequest1->getSheetMet($sheet->reveal())->shouldBeCalled()->willReturn($sheetMet1->reveal());
        $meetingRequest1
            ->getParticipants($sheetMet1->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $sheetMet2 = $this->prophesize(Sheet::class);
        $sheetMet2->getId()->shouldBeCalled()->willReturn(1337);
        $sheetMet2->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheetMet2->getTitle()->shouldBeCalled()->willReturn('Sheet 2');

        $sheetMet2Participant1 = $this->prophesize(Participant::class);
        $sheetMet2Participant1->getSheet()->shouldBeCalled()->willReturn($sheetMet2->reveal());
        $sheetMet2->getFirstParticipant()->shouldNotBeCalled();
        $sheetMet2->getUserParticipant($contact1)
            ->shouldBeCalled()
            ->willReturn($sheetMet2Participant1->reveal())
        ;

        $sheetMet2Participant2 = $this->prophesize(Participant::class);

        $sheetMet2ParticipantView1 = new MeetingParticipantView(
            'Robin',
            'Hood',
            'Archer',
            '+3377',
            'man',
            'robin@hood.example',
            3,
            ''
        );
        $sheetMet2ParticipantView2 = new MeetingParticipantView(
            'Jeanne',
            'Arc',
            'Ingeneer',
            '+331104',
            'woman',
            'jeanne@orleans.example',
            4,
            'Voice listener'
        );
        $this->participantsViewQueryHandler
            ->handle(
                new ParticipantsViewQuery(
                    [
                        $sheetMet2Participant1->reveal(),
                        $sheetMet2Participant2->reveal(),
                    ],
                    'fr',
                    [$contactScanned1->reveal(), $contactScanned2->reveal(), $contactScanned3->reveal()],
                    $sheet->reveal(),
                    $user
                )
            )
            ->shouldBeCalled()
            ->willReturn([$sheetMet2ParticipantView1, $sheetMet2ParticipantView2])
        ;

        $this->sheetInfoGuesser
            ->guessSheetInfos($sheetMet2->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(
                [
                    Tag::SHEET_TITLE => 'Sheet 2',
                    Tag::SHEET_ORGANIZATION_CATEGORY => 'Group',
                    Tag::SHEET_ORGANIZATION_STAFF => '19',
                    Tag::SHEET_ORGANIZATION_TURNOVER => '1M',
                    Tag::SHEET_WEBSITE => 'https://example.com',
                    Tag::SHEET_ADDRESS => '9 rue de Rivoli',
                    Tag::SHEET_ZIPCODE => '75001',
                    Tag::SHEET_CITY => 'Paris',
                    Tag::SHEET_COUNTRY => 'FR',
                ]
            )
        ;

        $meetingRequest2 = $this->prophesize(Request::class);
        $meetingRequest2->getSheetMet($sheet->reveal())->shouldBeCalled()->willReturn($sheetMet2->reveal());
        $meetingRequest2
            ->getParticipants($sheetMet2->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheetMet2Participant1->reveal(), $sheetMet2Participant2->reveal()])
        ;

        $this->requestRepository
            ->findApproved($sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$meetingRequest1->reveal(), $meetingRequest2->reveal()])
        ;

        $this->contactRepository
            ->findByEventAndUsers($event->reveal(), [$user])
            ->shouldBeCalled()
            ->willReturn([$contactScanned1->reveal(), $contactScanned2->reveal(), $contactScanned3->reveal()]);

        $this->sheetRepository
            ->getSheetsByUserAndEvent($contact1, $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheetMet2->reveal()])
        ;

        $sheetOfContact = $this->prophesize(Sheet::class);
        $sheetOfContact->getId()->shouldBeCalled()->willReturn(1984);
        $sheetOfContact->getTitle()->shouldBeCalled()->willReturn('Sheet 3');
        $participantOfContact1 = $this->prophesize(Participant::class);
        $participantOfContact1->getSheet()->shouldBeCalled()->willReturn($sheetOfContact->reveal());

        $this->sheetRepository
            ->getSheetsByUserAndEvent($contact2, $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheetOfContact->reveal()])
        ;
        $sheetOfContact->getUserParticipant($contact2)
            ->shouldBeCalled()
            ->willReturn($participantOfContact1->reveal())
        ;
        $sheetOfContact->getType()->shouldBeCalled()->willReturn($type->reveal());

        $participantOfContact2 = $this->prophesize(Participant::class);
        $participantOfContact2->getSheet()->shouldBeCalled()->willReturn($sheetOfContact->reveal());
        $this->sheetRepository
            ->getSheetsByUserAndEvent($contact3, $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheetOfContact->reveal()])
        ;
        $sheetOfContact->getUserParticipant($contact3)
            ->shouldBeCalled()
            ->willReturn($participantOfContact2->reveal())
        ;

        $this->sheetInfoGuesser
            ->guessSheetInfos($sheetOfContact->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(
                [
                    Tag::SHEET_TITLE => 'Sheet 3',
                    Tag::SHEET_ORGANIZATION_CATEGORY => 'PME',
                    Tag::SHEET_ORGANIZATION_STAFF => '9',
                    Tag::SHEET_ORGANIZATION_TURNOVER => '10k',
                    Tag::SHEET_WEBSITE => '',
                    Tag::SHEET_ADDRESS => '1 rue des Tulipes',
                    Tag::SHEET_ZIPCODE => '77560',
                    Tag::SHEET_CITY => 'Sous-les-Bois',
                    Tag::SHEET_COUNTRY => 'FR',
                ]
            )
        ;

        $participantOfContactView1 = new MeetingParticipantView(
            'Pablo',
            'Picasso',
            'painter',
            '+33999',
            'man',
            'pablo@picas.so',
            3,
            'Le cubisme'
        );
        $participantOfContactView2 = new MeetingParticipantView(
            'Paloma',
            'Picasso',
            'painter',
            '+33997',
            'woman',
            'paloma@picas.so',
            4,
            null
        );
        $this->participantsViewQueryHandler
            ->handle(
                new ParticipantsViewQuery(
                    [
                        $participantOfContact1->reveal(),
                        $participantOfContact2->reveal(),
                    ],
                    'fr',
                    [$contactScanned1->reveal(), $contactScanned2->reveal(), $contactScanned3->reveal()],
                    $sheet->reveal(),
                    $user
                )
            )
            ->shouldBeCalled()
            ->willReturn([$participantOfContactView1, $participantOfContactView2])
        ;

        $this->assertEquals(
            new MeetingSheetListView(
                [
                    42 => new MeetingSheetView(
                        'Sheet 1',
                        'Group',
                        '10M',
                        '100',
                        'https://website.com',
                        '10 rue Saint-Marc',
                        '75002',
                        'Paris',
                        'FR',
                        'Exposant',
                        true,
                        [
                            $sheetMet1ParticipantView,
                        ]
                    ),
                    1337 => new MeetingSheetView(
                        'Sheet 2',
                        'Group',
                        '1M',
                        '19',
                        'https://example.com',
                        '9 rue de Rivoli',
                        '75001',
                        'Paris',
                        'FR',
                        'Exposant',
                        true,
                        [
                            $sheetMet2ParticipantView1,
                            $sheetMet2ParticipantView2,
                        ]
                    ),
                    1984 => new MeetingSheetView(
                        'Sheet 3',
                        'PME',
                        '10k',
                        '9',
                        '',
                        '1 rue des Tulipes',
                        '77560',
                        'Sous-les-Bois',
                        'FR',
                        'Exposant',
                        false,
                        [
                            $participantOfContactView1,
                            $participantOfContactView2,
                        ]
                    ),
                ],
                'B2B Event'
            ),
            $this->meetingSheetViewQueryHandler->handle(
                new MeetingSheetViewQuery($event->reveal(), $user, $sheet->reveal(), 'fr')
            )
        );
    }
}
