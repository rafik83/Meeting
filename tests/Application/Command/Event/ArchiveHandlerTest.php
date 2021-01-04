<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Event\Archive;
use Proximum\Vimeet\Application\Command\Event\ArchiveHandler;
use Proximum\Vimeet\Domain\Exception\Event\DayNotDefinedException;
use Proximum\Vimeet\Domain\Exception\Event\EventAlreadyArchivedException;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ArchiveHandlerTest extends TestCase
{
    public function testHandleException()
    {
        $this->expectException(EventAlreadyArchivedException::class);

        // context
        $begin = new \DateTime('2017-04-04 10:00:00.000', new \DateTimeZone('UTC'));
        $end = new \DateTime('2017-04-04 19:00:00.000', new \DateTimeZone('UTC'));
        $event = EventFactory::createEvent();
        $day = new Day($event, $begin, $end);
        $event->setDays([$day]);
        $event->archive();

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set(Argument::any())->shouldNotBeCalled();

        // handler
        $archiveHandler = new ArchiveHandler($eventRepository->reveal());
        $archiveHandler->handle(new Archive($event));
    }

    public function testHandleNoDay()
    {
        $this->expectException(DayNotDefinedException::class);

        // context
        $event = EventFactory::createEvent();

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set(Argument::any())->shouldNotBeCalled();

        // handler
        $archiveHandler = new ArchiveHandler($eventRepository->reveal());
        $archiveHandler->handle(new Archive($event));
    }

    public function testHandleNoSuffix()
    {
        // context
        $begin = new \DateTime('2017-04-04 10:00:00.000', new \DateTimeZone('UTC'));
        $end = new \DateTime('2017-04-04 19:00:00.000', new \DateTimeZone('UTC'));
        $event = EventFactory::createEvent();
        $day = new Day($event, $begin, $end);
        $event->setDays([$day]);
        $event->setDomain('super-event-2017.vimeet.proximum');

        // Expected
        $expectedEvent = EventFactory::createEvent();
        $day = new Day($expectedEvent, $begin, $end);
        $expectedEvent->setDays([$day]);
        $expectedEvent->setDomain('super-event-2017.vimeet.proximum');
        $expectedEvent->archive();

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        // handler
        $archiveHandler = new ArchiveHandler($eventRepository->reveal());
        $archiveHandler->handle(new Archive($event));
    }

    public function testHandle()
    {
        // context
        $begin = new \DateTime('2017-04-04 10:00:00.000', new \DateTimeZone('UTC'));
        $end = new \DateTime('2017-04-04 19:00:00.000', new \DateTimeZone('UTC'));
        $event = EventFactory::createEvent();
        $day = new Day($event, $begin, $end);
        $event->setDays([$day]);

        // Expected
        $expectedEvent = EventFactory::createEvent();
        $day = new Day($expectedEvent, $begin, $end);
        $expectedEvent->setDays([$day]);
        $expectedEvent->setDomain('super-event-2017.vimeet.proximum');
        $expectedEvent->archive();

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        // handler
        $archiveHandler = new ArchiveHandler($eventRepository->reveal());
        $archiveHandler->handle(new Archive($event));
    }
}
