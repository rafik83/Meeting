<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\AvailabilityTimeRange;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\AvailabilityTimeRange\ListViewQuery;
use Proximum\Vimeet\Application\Query\AvailabilityTimeRange\ListViewQueryHandler;
use Proximum\Vimeet\Application\View\AvailabilityTimeRange\AvailabilityTimeRangeView;
use Proximum\Vimeet\Application\View\AvailabilityTimeRange\ListView;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;

class ListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $begin1 = new \DateTime('2017-10-10 10:00:00.000');
        $begin2 = new \DateTime('2017-10-10 13:00:00.000');
        $begin3 = new \DateTime('2017-10-11 10:00:00.000');
        $end1 = new \DateTime('2017-10-10 12:00:00.000');
        $end2 = new \DateTime('2017-10-10 17:00:00.000');
        $end3 = new \DateTime('2017-10-11 18:00:00.000');

        $event = $this->prophesize(Event::class);
        $availabilityTimeRange1 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange2 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange3 = $this->prophesize(AvailabilityTimeRange::class);

        $availabilityTimeRange1->getName()->willReturn('name1');
        $availabilityTimeRange2->getName()->willReturn('name2');
        $availabilityTimeRange3->getName()->willReturn('name3');
        $availabilityTimeRange1->getBegin()->willReturn($begin1);
        $availabilityTimeRange2->getBegin()->willReturn($begin2);
        $availabilityTimeRange3->getBegin()->willReturn($begin3);
        $availabilityTimeRange1->getEnd()->willReturn($end1);
        $availabilityTimeRange2->getEnd()->willReturn($end2);
        $availabilityTimeRange3->getEnd()->willReturn($end3);

        $availabilityTimeRangeRepository = $this->prophesize(AvailabilityTimeRangeRepositoryInterface::class);
        $availabilityTimeRangeRepository
            ->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$availabilityTimeRange1->reveal(), $availabilityTimeRange2->reveal(), $availabilityTimeRange3->reveal()])
        ;

        $handler = new ListViewQueryHandler($availabilityTimeRangeRepository->reveal());
        $result = $handler->handle(new ListViewQuery($event->reveal()));

        $expectedViews = [
            new AvailabilityTimeRangeView('name1', $begin1, $end1),
            new AvailabilityTimeRangeView('name2', $begin2, $end2),
            new AvailabilityTimeRangeView('name3', $begin3, $end3),
        ];
        $expected = new ListView($expectedViews);

        $this->assertEquals($expected, $result);
    }
}
