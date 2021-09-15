<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planning\Day;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planning\Day\MassViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\MassViewQueryHandler;
use Proximum\Vimeet\Application\View\Planning\Day\MassView;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;

class MassViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $mass = $this->prophesize(Mass::class);
        $locale = 'fr';

        $begin = new \DateTime('2017-10-10 10:00:00.000');
        $end = new \DateTime('2017-10-10 10:30:00.000');
        $mass->getBegin()->willReturn($begin);
        $mass->getEnd()->willReturn($end);
        $mass->getTitle($locale)->shouldBeCalled()->willReturn('mass title');

        $handler = new MassViewQueryHandler();
        $result = $handler->handle(new MassViewQuery($mass->reveal(), $locale));

        $expected = new MassView($begin, $end, 'mass title');
        $this->assertEquals($expected, $result);
    }
}
