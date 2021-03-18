<?php

namespace Proximum\Vimeet\Tests\Domain\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Exception\Event\EventDoesNotHaveLocale;
use Proximum\Vimeet\Domain\Exception\Event\EventNotFoundException;
use Proximum\Vimeet\Domain\Exception\Event\EventNotVisibleException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class EventByHostResolverTest extends TestCase
{
    /** @var ObjectProphecy */
    private $eventRepository;

    public function setUp()
    {
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
    }

    public function testResolveEventFromHostAndLocaleUnknownHost()
    {
        $this->expectException(EventNotFoundException::class);
        $this->eventRepository->getEventByDomain('test.vimeet.proximum')->shouldBeCalled()->willReturn(null);

        $resolver = new EventByHostResolver($this->eventRepository->reveal());
        $resolver->resolveEventFromHostAndLocale('test.vimeet.proximum', 'fr');
    }

    public function testResolveEventFromHostAndLocaleEventNotVisible()
    {
        $this->expectException(EventNotVisibleException::class);

        $event = $this->prophesize(Event::class);
        $event->isVisible()->willReturn(false);

        $this->eventRepository->getEventByDomain('test.vimeet.proximum')->shouldBeCalled()->willReturn($event);

        $resolver = new EventByHostResolver($this->eventRepository->reveal());
        $resolver->resolveEventFromHostAndLocale('test.vimeet.proximum', 'fr');
    }

    public function testResolveEventFromHostAndLocaleEventWithoutLocale()
    {
        $this->expectException(EventDoesNotHaveLocale::class);

        $event = $this->prophesize(Event::class);
        $event->isVisible()->willReturn(true);
        $event->hasLocale('fr')->willReturn(false);

        $this->eventRepository->getEventByDomain('test.vimeet.proximum')->shouldBeCalled()->willReturn($event);

        $resolver = new EventByHostResolver($this->eventRepository->reveal());
        $resolver->resolveEventFromHostAndLocale('test.vimeet.proximum', 'fr');
    }

    public function testResolveEventFromHostAndLocale()
    {
        $event = $this->prophesize(Event::class);
        $event->isVisible()->willReturn(true);
        $event->hasLocale('fr')->willReturn(true);

        $this->eventRepository->getEventByDomain('test.vimeet.proximum')->shouldBeCalled()->willReturn($event);

        $resolver = new EventByHostResolver($this->eventRepository->reveal());
        $result = $resolver->resolveEventFromHostAndLocale('test.vimeet.proximum', 'fr');

        $this->assertEquals($event->reveal(), $result);
    }
}
