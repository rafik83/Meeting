<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Event;

use Proximum\Vimeet\Application\Query\Event\EventListQuery;
use Proximum\Vimeet\Application\Query\Event\EventListQueryHandler;
use Proximum\Vimeet\Application\View\Event\DayView;
use Proximum\Vimeet\Application\View\Event\EventListsView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\View\EventListView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class EventListQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $startTime = new \DateTime();
        $endTime   = new \DateTime();
        $eventOne  = EventFactory::createEvent('event1');
        $day       = new Day($eventOne, $startTime, $endTime);
        $eventOne->setDays([$day]);

        $eventTwo     = EventFactory::createEvent('event2');
        $startTimeTwo = new \DateTime();
        $endTimeTwo   = new \DateTime();
        $startTimeTwo->modify('-2 month');
        $endTimeTwo->modify('-2 month');
        $dayTwo = new Day($eventTwo, $startTimeTwo, $endTimeTwo);
        $eventTwo->setDays([$dayTwo]);

        // Expected
        $currentEvent = new EventListView(
            null,
            'event1',
            'super-event.vimeet.proximum.dev',
            ['fr', 'en'],
            'fr',
            [new DayView($startTime, $endTime)]
        );

        $pastEvent = new EventListView(
            null,
            'event2',
            'super-event.vimeet.proximum.dev',
            ['fr', 'en'],
            'fr',
            [new DayView($startTimeTwo, $endTimeTwo)]
        );

        $expectedEventListsView = new EventListsView(
            [$currentEvent],
            [$pastEvent]
        );

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $admin           = $this->prophesize(Admin::class);
        $datetime        = new \DateTime();

        $eventRepository->getEventsWithDaysByAdmin($admin->reveal())
            ->shouldBeCalled()
            ->willReturn([
                $eventOne,
                $eventTwo,
            ]);

        $query   = new EventListQuery($admin->reveal());
        $handler = new EventListQueryHandler($eventRepository->reveal(), $datetime);

        $eventListsView = $handler->handle($query);

        $this->assertEquals($expectedEventListsView, $eventListsView);
        $this->assertCount(1, $eventListsView->currentsEventListView);
        $this->assertCount(1, $eventListsView->pastsEventListView);
    }
}
