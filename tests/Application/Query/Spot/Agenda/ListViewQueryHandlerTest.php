<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Spot\Agenda;

use Proximum\Vimeet\Application\Query\Spot\Agenda\ListViewQuery;
use Proximum\Vimeet\Application\Query\Spot\Agenda\ListViewQueryHandler;
use Proximum\Vimeet\Application\Query\Spot\Agenda\SpotViewQueryHandler;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ListViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();

        // Mock
        $spotRepository       = $this->prophesize(SpotRepositoryInterface::class);
        $spotViewQueryHandler = $this->prophesize(SpotViewQueryHandler::class);

        $spots = [];

        $spotRepository->findByEvent($event)->shouldBeCalled()->willReturn($spots);

        $query   = new ListViewQuery($event);
        $handler = new ListViewQueryHandler(
            $spotRepository->reveal(),
            $spotViewQueryHandler->reveal()
        );

        $handler->handle($query);
    }
}
