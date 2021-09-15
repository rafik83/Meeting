<?php

namespace Proximum\Vimeet\Tests\Domain\Happening\Webinar;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Happening\Webinar\MustBeAvailableToParticipate;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Configuration;
use Proximum\Vimeet\Domain\Model\Happening;

class MustBeAvailableToParticipateTest extends TestCase
{
    /** @var \DateTimeInterface */
    private $dateTime;

    protected function setUp()
    {
        $this->dateTime = \DateTime::createFromFormat('!Y-m-d H:i', '2020-03-21 12:00');
    }

    public function testIsSatisfiedIfEventNotVisio()
    {
        $eventConfiguration = $this->prophesize(Configuration::class);
        $eventConfiguration->isVisio()->shouldBeCalled()->willReturn(false);
        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->willReturn($eventConfiguration->reveal());
        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->willReturn($event->reveal());

        $specification = new MustBeAvailableToParticipate($this->dateTime);
        $this->assertTrue($specification->isSatisfiedBy($happening->reveal()));
    }

    public function testIsSatisfiedIfNotInHappeningHours()
    {
        $eventConfiguration = $this->prophesize(Configuration::class);
        $eventConfiguration->isVisio()->shouldBeCalled()->willReturn(true);
        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->willReturn($eventConfiguration->reveal());
        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->willReturn($event->reveal());
        $happening->getBegin()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-03-22 14:00'));
        $happening->getEnd()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-03-22 15:00'));

        $specification = new MustBeAvailableToParticipate($this->dateTime);
        $this->assertTrue($specification->isSatisfiedBy($happening->reveal()));
    }

    public function testIsNotSatisfiedIfInHappeningHours()
    {
        $eventConfiguration = $this->prophesize(Configuration::class);
        $eventConfiguration->isVisio()->shouldBeCalled()->willReturn(true);
        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->willReturn($eventConfiguration->reveal());
        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->willReturn($event->reveal());
        $happening->getBegin()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-03-21 11:30'));
        $happening->getEnd()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-03-21 12:30'));

        $specification = new MustBeAvailableToParticipate($this->dateTime);
        $this->assertFalse($specification->isSatisfiedBy($happening->reveal()));
    }
}
