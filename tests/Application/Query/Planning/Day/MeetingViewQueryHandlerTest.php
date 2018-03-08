<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Planning\Day;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planning\Day\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\View\Planning\Day\MeetingView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;

class MeetingViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $begin = new \DateTime('2017-10-10 10:00:00.000');
        $end = new \DateTime('2017-10-10 10:30:00.000');
        $user = $this->prophesize(User::class);
        $meeting = $this->prophesize(Meeting::class);
        $sheetA = $this->prophesize(Sheet::class);
        $sheetB = $this->prophesize(Sheet::class);
        $spot = $this->prophesize(Spot::class);
        $slot = $this->prophesize(MeetingSlot::class);

        $meeting->getSheetOfUser($user->reveal())->willReturn($sheetA->reveal());
        $meeting->getSpot()->willReturn($spot->reveal());
        $meeting->getSheetMet($sheetA->reveal())->willReturn($sheetB->reveal());
        $meeting->getSlot()->willReturn($slot->reveal());
        $spot->getReference()->willReturn('A1');
        $slot->getBegin()->willReturn($begin);
        $slot->getEnd()->willReturn($end);
        $sheetA->getTitle()->willReturn('sheetA');
        $sheetB->getTitle()->willReturn('sheetB');

        $handler = new MeetingViewQueryHandler();
        $result = $handler->handle(new MeetingViewQuery($meeting->reveal(), $user->reveal()));

        $expected = new MeetingView(
            $begin,
            $end,
            'A1',
            'sheetA',
            'sheetB'
        );

        $this->assertEquals($expected, $result);
    }
}
