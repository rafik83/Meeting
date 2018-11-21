<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
use Proximum\Vimeet\Application\View\Agenda\Admin\RequestSlotView;
use Proximum\Vimeet\Application\View\Agenda\Admin\RequestView;
use Proximum\Vimeet\Domain\Meeting\VisioGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

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

    public function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);

        $this->sheetMet = $this->prophesize(Sheet::class);
        $this->sheetMet->getId()->willReturn(42);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(11);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(22);

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
            ->guessParticipantCompleteName($participant1->reveal(), 'fr')
            ->willReturn('Korben DALLAS')
        ;
        $this->participantInfoGuesser
            ->guessParticipantCompleteName($participant2->reveal(), 'fr')
            ->willReturn('Leeloo')
        ;
        $this->meetingRequest
            ->getParticipants($this->sheet->reveal())
            ->willReturn([$participant1->reveal(), $participant2->reveal()])
        ;

        $this->requestSlotViewQueryHandler = $this->prophesize(RequestSlotViewQueryHandler::class);
        $this->visioGuesser = $this->prophesize(VisioGuesser::class);

        $this->requestViewQueryHandler = new RequestViewQueryHandler(
            $this->sheetInfoGuesser->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->requestSlotViewQueryHandler->reveal(),
            $this->visioGuesser->reveal()
        );
    }

    public function test_meeting_request_is_transformable_into_meeting()
    {
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
            ->shouldBeCalled()
            ->willReturn([new RequestSlotView([333, 444])])
        ;

        $this->assertEquals(
            new RequestView(
                1337,
                'Fifth Element Corp.',
                42,
                [new ParticipantView(11, 'Korben DALLAS'), new ParticipantView(22, 'Leeloo')],
                true,
                false
            ),
            $this->requestViewQueryHandler->handle(
                new RequestViewQuery($this->meetingRequest->reveal(), $this->sheet->reveal(), 'fr')
            )
        );
    }

    public function test_meeting_request_is_not_transformable_into_meeting_because_no_slot_available()
    {
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
                false
            ),
            $this->requestViewQueryHandler->handle(
                new RequestViewQuery($this->meetingRequest->reveal(), $this->sheet->reveal(), 'fr')
            )
        );
    }

    public function test_meeting_request_is_not_transformable_into_meeting_because_one_of_sheets_not_attend()
    {
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
                true
            ),
            $this->requestViewQueryHandler->handle(
                new RequestViewQuery($this->meetingRequest->reveal(), $this->sheet->reveal(), 'fr')
            )
        );
    }
}
