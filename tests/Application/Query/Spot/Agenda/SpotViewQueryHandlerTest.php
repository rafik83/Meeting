<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Spot\Agenda;

use Proximum\Vimeet\Application\Query\Spot\Agenda\SpotViewQuery;
use Proximum\Vimeet\Application\Query\Spot\Agenda\SpotViewQueryHandler;
use Proximum\Vimeet\Application\View\Spot\Agenda\SpotView;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SpotViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
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
