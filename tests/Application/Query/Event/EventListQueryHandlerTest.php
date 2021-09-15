<?php

namespace Proximum\Vimeet\Tests\Application\Query\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Event\EventListQuery;
use Proximum\Vimeet\Application\Query\Event\EventListQueryHandler;
use Proximum\Vimeet\Application\View\Event\DayView;
use Proximum\Vimeet\Application\View\Event\EventListsView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\View\EventListView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class EventListQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $eventRepository;

    /** @var ObjectProphecy */
    private $admin;

    /** @var \DateTime */
    private $dateTime;

    public function setUp()
    {
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->admin           = $this->prophesize(Admin::class);
        $this->dateTime        = new \DateTime('2017-04-04 10:10:00.000');
    }

    public function testHandleCurrent()
    {
        $eventOne  = EventFactory::createEvent('event1');
        $startTime = new \DateTime('2017-04-14 10:10:00.000');
        $endTime   = new \DateTime('2017-04-14 18:10:00.000');
        $day       = new Day($eventOne, $startTime, $endTime);
        $eventOne->setDays([$day]);

        $eventTwo = EventFactory::createEvent('event2', null, ['fr'], null, null, 2);

        $eventThree     = EventFactory::createEvent('event3');
        $startTimeThree = new \DateTime('2017-01-04 10:00:00.000');
        $endTimeThree   = new \DateTime('2017-01-04 18:00:00.000');
        $dayThree       = new Day($eventThree, $startTimeThree, $endTimeThree);
        $eventThree->setDays([$dayThree]);

        // Expected
        $currentEvent1 = new EventListView(
            1,
            'event1',
            'super-event.vimeet.proximum',
            ['fr', 'en'],
            'fr',
            true,
            [new DayView($startTime, $endTime)]
        );

        $currentEvent2 = new EventListView(
            2,
            'event2',
            'super-event.vimeet.proximum',
            ['fr'],
            'fr',
            true,
            []
        );
        $expectedEventListsView = new EventListsView([$currentEvent1, $currentEvent2]);

        $this->eventRepository
            ->getEventsWithDaysByAdmin($this->admin->reveal())
            ->shouldBeCalled()
            ->willReturn([$eventOne, $eventTwo, $eventThree]);

        $query   = new EventListQuery($this->admin->reveal(), EventListQuery::STATE_CURRENT);
        $handler = new EventListQueryHandler($this->eventRepository->reveal(), $this->dateTime);

        $eventListsView = $handler->handle($query);

        $this->assertEquals($expectedEventListsView, $eventListsView);
    }

    public function testHandlePast()
    {
        $eventOne  = EventFactory::createEvent('event1');
        $startTime = new \DateTime('2017-04-14 10:10:00.000');
        $endTime   = new \DateTime('2017-04-14 18:10:00.000');
        $day       = new Day($eventOne, $startTime, $endTime);
        $eventOne->setDays([$day]);

        $eventTwo = EventFactory::createEvent('event2', null, ['fr'], null, null, 2);

        $eventThree     = EventFactory::createEvent('event3', null, ['fr'], null, null, 3);
        $startTimeThree = new \DateTime('2017-01-04 10:00:00.000');
        $endTimeThree   = new \DateTime('2017-01-04 18:00:00.000');
        $dayThree       = new Day($eventThree, $startTimeThree, $endTimeThree);
        $eventThree->setDays([$dayThree]);

        // Expected
        $expectedEventListsView = new EventListsView([
            new EventListView(
                3,
                'event3',
                'super-event.vimeet.proximum',
                ['fr'],
                'fr',
                true,
                [new DayView($startTimeThree, $endTimeThree)]
            ),
        ]);

        $this->eventRepository
            ->getEventsWithDaysByAdmin($this->admin->reveal())
            ->shouldBeCalled()
            ->willReturn([$eventOne, $eventTwo, $eventThree]);

        $query   = new EventListQuery($this->admin->reveal(), EventListQuery::STATE_PAST);
        $handler = new EventListQueryHandler($this->eventRepository->reveal(), $this->dateTime);

        $eventListsView = $handler->handle($query);

        $this->assertEquals($expectedEventListsView, $eventListsView);
    }

    public function testHandleArchived()
    {
        $eventOne  = EventFactory::createEvent('event1');
        $startTime = new \DateTime('2017-04-14 10:10:00.000');
        $endTime   = new \DateTime('2017-04-14 18:10:00.000');
        $day       = new Day($eventOne, $startTime, $endTime);
        $eventOne->setDays([$day]);

        $eventThree     = EventFactory::createEvent('event3', null, ['fr'], null, null, 3);
        $startTimeThree = new \DateTime('2017-01-04 10:00:00.000');
        $endTimeThree   = new \DateTime('2017-01-04 18:00:00.000');
        $dayThree       = new Day($eventThree, $startTimeThree, $endTimeThree);
        $eventThree->setDays([$dayThree]);

        // Expected
        $expectedEventListsView = new EventListsView([
            new EventListView(
                1,
                'event1',
                'super-event.vimeet.proximum',
                ['fr', 'en'],
                'fr',
                true,
                [new DayView($startTime, $endTime)]
            ),
            new EventListView(
                3,
                'event3',
                'super-event.vimeet.proximum',
                ['fr'],
                'fr',
                true,
                [new DayView($startTimeThree, $endTimeThree)]
            ),
        ]);

        $this->eventRepository
            ->findArchivedByAdmin($this->admin->reveal())
            ->shouldBeCalled()
            ->willReturn([$eventOne, $eventThree]);

        $query   = new EventListQuery($this->admin->reveal(), EventListQuery::STATE_ARCHIVED);
        $handler = new EventListQueryHandler($this->eventRepository->reveal(), $this->dateTime);

        $eventListsView = $handler->handle($query);

        $this->assertEquals($expectedEventListsView, $eventListsView);
    }
}
