<?php

namespace Proximum\Vimeet\Tests\Application\Query\Meeting;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Meeting\MeetingSheetViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\MeetingSheetViewQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Meeting\ParticipantsViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\ParticipantsViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\MeetingParticipantView;
use Proximum\Vimeet\Application\View\Meeting\MeetingSheetListView;
use Proximum\Vimeet\Application\View\Meeting\MeetingSheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class MeetingSheetViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy|RequestRepositoryInterface */
    private $requestRepository;

    /** @var ObjectProphecy|ParticipantsViewQueryHandler */
    private $participantsViewQueryHandler;

    /** @var ObjectProphecy|SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var MeetingSheetViewQueryHandler */
    private $meetingSheetViewQueryHandler;

    public function setUp()
    {
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->participantsViewQueryHandler = $this->prophesize(ParticipantsViewQueryHandler::class);
        $this->sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);

        $this->meetingSheetViewQueryHandler = new MeetingSheetViewQueryHandler(
            $this->requestRepository->reveal(),
            $this->participantsViewQueryHandler->reveal(),
            $this->sheetInfoGuesser->reveal()
        );
    }

    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getTitle()->shouldBeCalled()->willReturn('B2B Event');

        $sheet = $this->prophesize(Sheet::class);

        $type = $this->prophesize(Type::class);
        $type->getTitle('fr')->shouldBeCalled()->willReturn('Exposant');

        $sheetMet1Participant = $this->prophesize(Participant::class);
        $sheetMet1 = $this->prophesize(Sheet::class);
        $sheetMet1->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheetMet1->getParticipantsArray()->shouldBeCalled()->willReturn([$sheetMet1Participant->reveal()]);
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
            'korben@taxi.space'
        );
        $this->participantsViewQueryHandler
            ->handle(
                new ParticipantsViewQuery([$sheetMet1Participant->reveal()], 'fr')
            )
            ->shouldBeCalled()
            ->willReturn([$sheetMet1ParticipantView])
        ;

        $meetingRequest1 = $this->prophesize(Request::class);
        $meetingRequest1->getSheetMet($sheet->reveal())->shouldBeCalled()->willReturn($sheetMet1->reveal());

        $sheetMet2Participant1 = $this->prophesize(Participant::class);
        $sheetMet2Participant2 = $this->prophesize(Participant::class);
        $sheetMet2 = $this->prophesize(Sheet::class);
        $sheetMet2->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheetMet2->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn(
                [
                    $sheetMet2Participant1->reveal(),
                    $sheetMet2Participant2->reveal(),
                ]
            )
        ;
        $sheetMet2ParticipantView1 = new MeetingParticipantView(
            'Robin',
            'Hood',
            'Archer',
            '+3377',
            'man',
            'robin@hood.example'
        );
        $sheetMet2ParticipantView2 = new MeetingParticipantView(
            'Jeanne',
            'Arc',
            'Ingeneer',
            '+331104',
            'woman',
            'jeanne@orleans.example'
        );
        $this->participantsViewQueryHandler
            ->handle(
                new ParticipantsViewQuery(
                    [
                        $sheetMet2Participant1->reveal(),
                        $sheetMet2Participant2->reveal(),
                    ],
                    'fr'
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

        $this->requestRepository
            ->findApproved($sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$meetingRequest1->reveal(), $meetingRequest2->reveal()])
        ;

        $this->assertEquals(
            new MeetingSheetListView(
                [
                    new MeetingSheetView(
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
                        [
                            $sheetMet1ParticipantView,
                        ]
                    ),
                    new MeetingSheetView(
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
                        [
                            $sheetMet2ParticipantView1,
                            $sheetMet2ParticipantView2,
                        ]
                    ),
                ],
                'B2B Event'
            ),
            $this->meetingSheetViewQueryHandler->handle(
                new MeetingSheetViewQuery($event->reveal(), $sheet->reveal(), 'fr')
            )
        );
    }
}
