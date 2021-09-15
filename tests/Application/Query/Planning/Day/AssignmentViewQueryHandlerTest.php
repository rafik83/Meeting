<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planning\Day;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planning\Day\AssignmentViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\AssignmentViewQueryHandler;
use Proximum\Vimeet\Application\View\Planning\Day\AssignmentView;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

class AssignmentViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $locale = 'fr';
        $begin = new \DateTime('2017-10-10 10:00:00.000');
        $end = new \DateTime('2017-10-10 10:30:00.000');
        $assignment = $this->prophesize(MassAssignment::class);
        $mass = $this->prophesize(Mass::class);
        $assignment->getBegin()->willReturn($begin);
        $assignment->getEnd()->willReturn($end);
        $assignment->getMass()->willReturn($mass->reveal());
        $mass->getTitle('fr')->shouldBeCalled()->willReturn('Mass title');

        $handler = new AssignmentViewQueryHandler();
        $result = $handler->handle(new AssignmentViewQuery($assignment->reveal(), $locale));

        $expected = new AssignmentView($begin, $end, 'Mass title');
        $this->assertEquals($expected, $result);
    }
}
