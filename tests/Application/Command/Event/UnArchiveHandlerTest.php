<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Event\UnArchive;
use Proximum\Vimeet\Application\Command\Event\UnArchiveHandler;
use Proximum\Vimeet\Domain\Exception\Event\EventNotArchivedException;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UnArchiveHandlerTest extends TestCase
{
    public function testHandleException()
    {
        $this->expectException(EventNotArchivedException::class);

        // context
        $event = EventFactory::createEvent('title');

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set(Argument::any())->shouldNotBeCalled();

        // handler
        $unArchiveHandler = new UnArchiveHandler($eventRepository->reveal());
        $unArchiveHandler->handle(new UnArchive($event));
    }

    public function testHandle()
    {
        // context
        $event = EventFactory::createEvent('title');
        $event->archive();

        // Expected
        $expectedEvent = EventFactory::createEvent('title');

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        // handler
        $unArchiveHandler = new UnArchiveHandler($eventRepository->reveal());
        $unArchiveHandler->handle(new UnArchive($event));
    }

    public function testHandleWithDays()
    {
        // context
        $day = $this->prophesize(Day::class);
        $day->getStartTime()->willReturn(new \DateTime('2012-10-10 10:10:10.000'));
        $event = EventFactory::createEvent('title');
        $event->setDays([$day->reveal()]);
        $event->setDomain('title-2012.vimeet.proximum.dev');
        $event->archive();

        // Expected
        $expectedEvent = EventFactory::createEvent('title');
        $expectedEvent->setDays([$day->reveal()]);
        $expectedEvent->setDomain('title.vimeet.proximum.dev');

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        // handler
        $unArchiveHandler = new UnArchiveHandler($eventRepository->reveal());
        $unArchiveHandler->handle(new UnArchive($event));
    }
}
