<?php

namespace Proximum\Vimeet\Tests\Application\Query\MultipleSheets\Request;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\RequestViewQuery;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\RequestViewQueryHandler;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\ParticipantView;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\RequestView;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class RequestViewQueryHandlerTest extends TestCase
{
    public function testHandleWithNoPreference()
    {
        $locale  = 'fr';
        $sheet   = $this->prophesize(Sheet::class);
        $request = $this->prophesize(Request::class);
        $sheetMet = $this->prophesize(Sheet::class);
        $request->getId()->willReturn(123);
        $sheetMet->getId()->willReturn(432);
        $sheetMet->getTitle()->willReturn('sheet Met title');
        $request->getState()->willReturn('sent');
        $request->getSheetMet($sheet->reveal())->willReturn($sheetMet);
        $request->isSender($sheetMet->reveal())->willReturn(false);
        $request->getParticipantsOfSheetInRequest($sheetMet->reveal())->willReturn([]);
        $request->hasMeeting()->willReturn(false);
        $participantViewQueryHandler = $this->prophesize(ParticipantViewQueryHandler::class);

        $handler = new RequestViewQueryHandler($participantViewQueryHandler->reveal());
        $result = $handler->handle(
            new RequestViewQuery($sheet->reveal(), $request->reveal(), $locale)
        );

        $expected = new RequestView(
            123,
            $request->reveal(),
            432,
            'sheet Met title',
            $sheetMet->reveal(),
            'sent',
            RequestView::TYPE_PROPOSITION,
            [],
            false
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandle()
    {
        $locale  = 'fr';
        $sheet   = $this->prophesize(Sheet::class);
        $request = $this->prophesize(Request::class);
        $sheetMet = $this->prophesize(Sheet::class);
        $request->getId()->willReturn(123);
        $sheetMet->getId()->willReturn(432);
        $sheetMet->getTitle()->willReturn('sheet Met title');
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $request->getState()->willReturn('approved');
        $request->isSender($sheetMet->reveal())->willReturn(true);
        $request->getSheetMet($sheet->reveal())->willReturn($sheetMet);
        $request->getParticipantsOfSheetInRequest($sheetMet->reveal())->willReturn([
            $participant1->reveal(),
            $participant2->reveal(),
        ]);
        $request->hasMeeting()->willReturn(true);
        $participantViewQueryHandler = $this->prophesize(ParticipantViewQueryHandler::class);
        $participantView1 = new ParticipantView('complete Name 1');
        $participantView2 = new ParticipantView('complete Name 2');
        $participantViewQueryHandler
            ->handle(new ParticipantViewQuery($participant1->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($participantView1);
        $participantViewQueryHandler
            ->handle(new ParticipantViewQuery($participant2->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($participantView2);

        $handler = new RequestViewQueryHandler($participantViewQueryHandler->reveal());
        $result = $handler->handle(
            new RequestViewQuery($sheet->reveal(), $request->reveal(), $locale)
        );

        $expected = new RequestView(
            123,
            $request->reveal(),
            432,
            'sheet Met title',
            $sheetMet->reveal(),
            'approved',
            RequestView::TYPE_REQUEST,
            [$participantView1, $participantView2],
            true
        );

        $this->assertEquals($expected, $result);
    }
}
