<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Admin;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSlotAvailableException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\ParticipantView;
use Proximum\Vimeet\Application\View\Agenda\Admin\RequestView;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Meeting\VisioGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Ui\Helper\HasMeetingWithLinkedSheets;

class RequestViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheetInfoGuesser;

    /** @var ObjectProphecy */
    private $participantInfoGuesser;

    /** @var ObjectProphecy */
    private $requestSlotViewQueryHandler;

    /** @var ObjectProphecy */
    private $visioGuesser;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $sheetMet;

    /** @var ObjectProphecy */
    private $meetingRequest;

    /** @var RequestViewQueryHandler */
    private $requestViewQueryHandler;

    /** @var Type */
    private $type;

    /**@var Participant */
    private $participant1;

    /**@var Participant */
    private $participant2;

    /** @var ObjectProphecy|MeetingParticipants */
    private $meetingParticipants;

    /** @var HasMeetingWithLinkedSheets */
    private $hasMeetingWithLinkedSheets;

    public function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);
        $this->type = $this->prophesize(Type::class);
        $this->type->areAllSheetParticipantsAssignedToMeeting()->willReturn(false);
        $this->sheet->getType()->willReturn($this->type->reveal());

        $this->sheetMet = $this->prophesize(Sheet::class);
        $this->sheetMet->getId()->willReturn(42);
        $this->sheetMet->hasOnlyOneParticipant()->willReturn(false);

        $this->participant1 = $this->prophesize(Participant::class);
        $this->participant1->getId()->shouldBeCalled()->willReturn(11);

        $this->participant2 = $this->prophesize(Participant::class);
        $this->participant2->getId()->shouldBeCalled()->willReturn(22);

        $this->meetingRequest = $this->prophesize(Request::class);
        $this->meetingRequest->getSheetMet($this->sheet->reveal())->willReturn($this->sheetMet->reveal());
        $this->meetingRequest->getId()->willReturn(1337);

        $this->sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $this->sheetInfoGuesser
            ->guessSheetTitle($this->sheetMet->reveal(), 'fr')
            ->willReturn('Fifth Element Corp.')
        ;

        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $this->participantInfoGuesser
            ->guessParticipantCompleteName($this->participant1->reveal(), 'fr')
            ->willReturn('Korben DALLAS')
        ;
        $this->participantInfoGuesser
            ->guessParticipantCompleteName($this->participant2->reveal(), 'fr')
            ->willReturn('Leeloo')
        ;

        $this->meetingParticipants = $this->prophesize(MeetingParticipants::class);
        $this->meetingParticipants
            ->getMeetingParticipants($this->meetingRequest->reveal(), $this->sheet->reveal())
            ->willReturn([$this->participant1->reveal(), $this->participant2->reveal()])
        ;

        $this->requestSlotViewQueryHandler = $this->prophesize(RequestSlotViewQueryHandler::class);
        $this->visioGuesser = $this->prophesize(VisioGuesser::class);

        $this->hasMeetingWithLinkedSheets = $this->prophesize(HasMeetingWithLinkedSheets::class);

        $this->requestViewQueryHandler = new RequestViewQueryHandler(
            $this->sheetInfoGuesser->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->requestSlotViewQueryHandler->reveal(),
            $this->visioGuesser->reveal(),
            $this->meetingParticipants->reveal(),
            $this->hasMeetingWithLinkedSheets->reveal()
        );
    }

    public function test_meeting_request_is_transformable_into_meeting()
    {
        $this->sheet->hasOnlyOneParticipant()->willReturn(true);
        $this->sheet->hasLinkedSheets()->willReturn(false);
        $this->sheetMet->hasLinkedSheets()->willReturn(false);

        $this->hasMeetingWithLinkedSheets
            ->isSatisfiedBy($this->meetingRequest->reveal())
            ->shouldBeCalled()
            ->willReturn(false);


        $this->meetingRequest
            ->hasNoPreference($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->meetingRequest
            ->hasNoPreference($this->sheetMet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->visioGuesser
            ->hasMeetingRequestParticipantVisio($this->meetingRequest->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $this->meetingRequest
            ->isTransformableIntoMeeting()
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->meetingRequest
            ->isOneOfSheetsNotAttend()
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->assertEquals(
            new RequestView(
                1337,
                'Fifth Element Corp.',
                42,
                [new ParticipantView(11, 'Korben DALLAS'), new ParticipantView(22, 'Leeloo')],
                true,
                false,
                false,
                false
            ),
            $this->requestViewQueryHandler->handle(
                new RequestViewQuery($this->meetingRequest->reveal(), $this->sheet->reveal(), 'fr')
            )
        );
    }

    public function test_meeting_request_is_not_transformable_into_meeting_because_no_slot_available()
    {
        $this->sheet->hasOnlyOneParticipant()->willReturn(true);
        $this->sheet->hasLinkedSheets()->willReturn(false);
        $this->sheetMet->hasLinkedSheets()->willReturn(false);

        $this->hasMeetingWithLinkedSheets
            ->isSatisfiedBy($this->meetingRequest->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->meetingRequest
            ->hasNoPreference($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->meetingRequest
            ->hasNoPreference($this->sheetMet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->visioGuesser
            ->hasMeetingRequestParticipantVisio($this->meetingRequest->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->meetingRequest
            ->isTransformableIntoMeeting()
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->meetingRequest
            ->isOneOfSheetsNotAttend()
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->requestSlotViewQueryHandler
            ->handle(new RequestSlotViewQuery($this->meetingRequest->reveal(), true))
            ->shouldBeCalled()
            ->willThrow(NoSlotAvailableException::class)
        ;

        $this->assertEquals(
            new RequestView(
                1337,
                'Fifth Element Corp.',
                42,
                [new ParticipantView(11, 'Korben DALLAS'), new ParticipantView(22, 'Leeloo')],
                false,
                false,
                false,
                false
            ),
            $this->requestViewQueryHandler->handle(
                new RequestViewQuery($this->meetingRequest->reveal(), $this->sheet->reveal(), 'fr')
            )
        );
    }

    public function test_meeting_request_is_not_transformable_into_meeting_because_one_of_sheets_not_attend()
    {
        $this->sheet->hasOnlyOneParticipant()->willReturn(false);

        $this->hasMeetingWithLinkedSheets->isSatisfiedBy($this->meetingRequest->reveal())
            ->shouldNotBeCalled();

        $this->meetingRequest
            ->hasNoPreference($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->meetingRequest
            ->hasNoPreference($this->sheetMet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->visioGuesser
            ->hasMeetingRequestParticipantVisio($this->meetingRequest->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->meetingRequest
            ->isTransformableIntoMeeting()
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $this->meetingRequest
            ->isOneOfSheetsNotAttend()
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->requestSlotViewQueryHandler
            ->handle(new RequestSlotViewQuery($this->meetingRequest->reveal(), true))
            ->shouldNotBeCalled()
        ;

        $this->assertEquals(
            new RequestView(
                1337,
                'Fifth Element Corp.',
                42,
                [new ParticipantView(11, 'Korben DALLAS'), new ParticipantView(22, 'Leeloo')],
                false,
                true,
                true,
                false
            ),
            $this->requestViewQueryHandler->handle(
                new RequestViewQuery($this->meetingRequest->reveal(), $this->sheet->reveal(), 'fr')
            )
        );
    }

    public function test_not_no_preference_while_all_sheet_participants_are_assigned_to_meeting(): void
    {
        // override setup prophecies
        $this->sheet->hasOnlyOneParticipant()->willReturn(false);

        $this->meetingParticipants
            ->getMeetingParticipants($this->meetingRequest->reveal(), $this->sheet->reveal())
            ->willReturn([])
        ;

        $this->participant1->getId()->shouldNotBeCalled();
        $this->participant2->getId()->shouldNotBeCalled();

        // next
        $this->hasMeetingWithLinkedSheets
            ->isSatisfiedBy($this->meetingRequest->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->type->areAllSheetParticipantsAssignedToMeeting()->willReturn(true);

        $this->meetingRequest
            ->hasNoPreference($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->meetingRequest
            ->hasNoPreference($this->sheetMet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->visioGuesser
            ->hasMeetingRequestParticipantVisio($this->meetingRequest->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $this->meetingRequest
            ->isTransformableIntoMeeting()
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->meetingRequest
            ->isOneOfSheetsNotAttend()
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $this->assertEquals(
            new RequestView(
                1337,
                'Fifth Element Corp.',
                42,
                [],
                true,
                false,
                false,
                false
            ),
            $this->requestViewQueryHandler->handle(
                new RequestViewQuery($this->meetingRequest->reveal(), $this->sheet->reveal(), 'fr')
            )
        );
    }

    public function test_meeting_request_is_not_transformable_into_meeting_because_sheet_is_linked_sheet()
    {
        $this->sheet->hasOnlyOneParticipant()->willReturn(true);
        $this->sheet->hasLinkedSheets()->willReturn(true);
        $this->sheetMet->hasLinkedSheets()->willReturn(false);

        $this->hasMeetingWithLinkedSheets
            ->isSatisfiedBy($this->meetingRequest->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->meetingRequest
            ->hasNoPreference($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->meetingRequest
            ->hasNoPreference($this->sheetMet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->visioGuesser
            ->hasMeetingRequestParticipantVisio($this->meetingRequest->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $this->meetingRequest
            ->isTransformableIntoMeeting()
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->meetingRequest
            ->isOneOfSheetsNotAttend()
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->requestSlotViewQueryHandler
            ->handle(new RequestSlotViewQuery($this->meetingRequest->reveal(), false))
            ->shouldNotBeCalled()
        ;

        $this->assertEquals(
            new RequestView(
                1337,
                'Fifth Element Corp.',
                42,
                [new ParticipantView(11, 'Korben DALLAS'), new ParticipantView(22, 'Leeloo')],
                false,
                false,
                false,
                false
            ),
            $this->requestViewQueryHandler->handle(
                new RequestViewQuery($this->meetingRequest->reveal(), $this->sheet->reveal(), 'fr')
            )
        );
    }
}
