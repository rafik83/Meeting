<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planning\Day;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planning\Day\UnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\UnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\View\Planning\Day\UnavailabilityView;
use Proximum\Vimeet\Domain\Model\Unavailability;

class UnavailabilityViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $begin = new \DateTime('2017-10-10 10:00:00.000');
        $end = new \DateTime('2017-10-10 10:30:00.000');
        $unavailability = $this->prophesize(Unavailability::class);
        $unavailability->getBegin()->willReturn($begin);
        $unavailability->getEnd()->willReturn($end);
        $unavailability->getMessage()->willReturn('Message of unavailability');

        $handler = new UnavailabilityViewQueryHandler();
        $result = $handler->handle(new UnavailabilityViewQuery($unavailability->reveal()));

        $expected = new UnavailabilityView($begin, $end, 'Message of unavailability');
        $this->assertEquals($expected, $result);
    }
}
