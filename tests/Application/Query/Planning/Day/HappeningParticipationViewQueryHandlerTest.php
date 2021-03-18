<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planning\Day;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planning\Day\HappeningParticipationViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\HappeningParticipationViewQueryHandler;
use Proximum\Vimeet\Application\View\Planning\Day\HappeningParticipationView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;

class HappeningParticipationViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $locale = 'fr';
        $begin = new \DateTime('2017-10-10 10:00:00.000');
        $end = new \DateTime('2017-10-10 10:30:00.000');
        $happeningParticipation = $this->prophesize(HappeningParticipation::class);
        $happening = $this->prophesize(Happening::class);
        $happeningParticipation->getHappening()->willReturn($happening->reveal());
        $happening->getBegin()->willReturn($begin);
        $happening->getEnd()->willReturn($end);
        $happening->getTitle('fr')->shouldBeCalled()->willReturn('Happening title');

        $handler = new HappeningParticipationViewQueryHandler();
        $result = $handler->handle(new HappeningParticipationViewQuery($happeningParticipation->reveal(), $locale));

        $expected = new HappeningParticipationView($begin, $end, 'Happening title');
        $this->assertEquals($expected, $result);
    }
}
