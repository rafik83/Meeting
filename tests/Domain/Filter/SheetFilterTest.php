<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Filter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Filter\SheetFilter;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Session\Session;

class SheetFilterTest extends TestCase
{
    public function testGet()
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(1);

        $sessionMock = $this->prophesize(Session::class);
        $sessionMock->get('sheet_filters_1')->shouldBeCalled()->willReturn(['myfilter' => true]);

        $sheetFilter = new SheetFilter($sessionMock->reveal());
        $this->assertEquals(['myfilter' => true], $sheetFilter->get($event->reveal()));
    }

    public function testAdd()
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(2);

        $sessionMock = $this->prophesize(Session::class);
        $sessionMock->set('sheet_filters_2', ['myfilters' => true])->shouldBeCalled();

        $sheetFilter = new SheetFilter($sessionMock->reveal());
        $sheetFilter->add($event->reveal(), ['myfilters' => true]);
    }

    public function testClear()
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(3);

        $sessionMock = $this->prophesize(Session::class);
        $sessionMock->remove('sheet_filters_3')->shouldBeCalled();

        $sheetFilter = new SheetFilter($sessionMock->reveal());
        $sheetFilter->clear($event->reveal());
    }
}
