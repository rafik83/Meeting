<?php

namespace Proximum\Vimeet\Tests\Application\Command\AvailabilityTimeRange;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\AvailabilityTimeRange\Create;
use Proximum\Vimeet\Application\Command\AvailabilityTimeRange\CreateHandler;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->hasDay()->willReturn(false);
        $day = $this->prophesize(Event\Day::class);
        $begin = new \DateTime('2017-10-10 10:10:00.000');
        $end = new \DateTime('2017-10-10 18:00:00.000');
        $day->getBegin()->willReturn($begin);
        $day->getEnd()->willReturn($end);
        $inBetween = new \DateTime('2017-10-10 14:00:00.000');

        $availabilityTimeRangeRepository = $this->prophesize(AvailabilityTimeRangeRepositoryInterface::class);

        $create = new Create($event->reveal());
        $create->name = 'Name';
        $create->begin = $inBetween;
        $create->end = $end;

        $expected = new AvailabilityTimeRange($event->reveal(), 'Name', $inBetween, $end);
        $availabilityTimeRangeRepository->add($expected)->shouldBeCalled();

        $handler = new CreateHandler($availabilityTimeRangeRepository->reveal());
        $handler->handle($create);
    }
}
