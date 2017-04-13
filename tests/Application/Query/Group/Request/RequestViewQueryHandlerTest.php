<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Group\Request;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Group\Request\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Group\Request\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\Query\Group\Request\RequestViewQuery;
use Proximum\Vimeet\Application\Query\Group\Request\RequestViewQueryHandler;
use Proximum\Vimeet\Application\View\Group\Request\ParticipantView;
use Proximum\Vimeet\Application\View\Group\Request\RequestView;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class RequestViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWithNoPreference()
    {
        $locale  = 'fr';
        $sheet   = $this->prophesize(Sheet::class);
        $request = $this->prophesize(Request::class);
        $sheetMet = $this->prophesize(Sheet::class);
        $request->getId()->willReturn(123);
        $sheetMet->getId()->willReturn(432);
        $request->getState()->willReturn('sent');
        $request->getSheetMet($sheet->reveal())->willReturn($sheetMet);
        $request->isSender($sheetMet->reveal())->willReturn(false);
        $request->getParticipantsOfSheetInRequest($sheetMet->reveal())->willReturn([]);
        $request->hasMeeting()->willReturn(false);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesserCache::class);
        $sheetInfoGuesser->guessSheetTitle($sheetMet->reveal(), $locale)->shouldBeCalled()->willReturn('sheet Met title');
        $participantViewQueryHandler = $this->prophesize(ParticipantViewQueryHandler::class);

        $handler = new RequestViewQueryHandler($sheetInfoGuesser->reveal(), $participantViewQueryHandler->reveal());
        $result = $handler->handle(
            new RequestViewQuery($sheet->reveal(), $request->reveal(), $locale)
        );

        $expected = new RequestView(
            123,
            432,
            'sheet Met title',
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
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $request->getState()->willReturn('approved');
        $request->isSender($sheetMet->reveal())->willReturn(true);
        $request->getSheetMet($sheet->reveal())->willReturn($sheetMet);
        $request->getParticipantsOfSheetInRequest($sheetMet->reveal())->willReturn([
            $participant1->reveal(),
            $participant2->reveal()
        ]);
        $request->hasMeeting()->willReturn(true);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesserCache::class);
        $sheetInfoGuesser->guessSheetTitle($sheetMet->reveal(), $locale)->shouldBeCalled()->willReturn('sheet Met title');
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

        $handler = new RequestViewQueryHandler($sheetInfoGuesser->reveal(), $participantViewQueryHandler->reveal());
        $result = $handler->handle(
            new RequestViewQuery($sheet->reveal(), $request->reveal(), $locale)
        );

        $expected = new RequestView(
            123,
            432,
            'sheet Met title',
            'approved',
            RequestView::TYPE_REQUEST,
            [$participantView1, $participantView2],
            true
        );

        $this->assertEquals($expected, $result);
    }
}
