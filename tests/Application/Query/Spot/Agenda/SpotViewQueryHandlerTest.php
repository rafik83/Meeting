<?php

namespace Proximum\Vimeet\Tests\Application\Query\Spot\Agenda;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Spot\Agenda\SpotViewQuery;
use Proximum\Vimeet\Application\Query\Spot\Agenda\SpotViewQueryHandler;
use Proximum\Vimeet\Application\View\Spot\Agenda\SpotView;
use Proximum\Vimeet\Domain\Model\Spot;

class SpotViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $spot = $this->prophesize(Spot::class);

        $spot->getId()->willReturn(1);
        $spot->getReference()->willReturn('A01');
        $spot->isVisio()->willReturn(false);

        // expected
        $expectedSpotView = new SpotView(1, 'A01', false);

        $query   = new SpotViewQuery($spot->reveal());
        $handler = new SpotViewQueryHandler();

        $spotView = $handler->handle($query);

        $this->assertEquals($expectedSpotView, $spotView);
    }
}
