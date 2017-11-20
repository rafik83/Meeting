<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Time;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Time\TimeOverlap;
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

class TimeOverlapTest extends TestCase
{
    public function testContain()
    {
        $needle = $this->prophesize(TimeRangeInterface::class);
        $needle->getBegin()->willReturn(new \DateTime('2017-11-20 16:52:00'));
        $needle->getEnd()->willReturn(new \DateTime('2017-11-20 16:53:00'));

        $haystack = $this->prophesize(TimeRangeInterface::class);
        $haystack->getBegin()->willReturn(new \DateTime('2017-11-20 08:00:00'));
        $haystack->getEnd()->willReturn(new \DateTime('2017-11-20 20:00:00'));

        $this->assertTrue(TimeOverlap::contains($needle->reveal(), $haystack->reveal()));
    }

    public function testContainWithNeedleAndHaystackAreEquals()
    {
        $needle = $this->prophesize(TimeRangeInterface::class);
        $needle->getBegin()->willReturn(new \DateTime('2017-11-20 08:00:00'));
        $needle->getEnd()->willReturn(new \DateTime('2017-11-20 20:00:00'));

        $haystack = $this->prophesize(TimeRangeInterface::class);
        $haystack->getBegin()->willReturn(new \DateTime('2017-11-20 08:00:00'));
        $haystack->getEnd()->willReturn(new \DateTime('2017-11-20 20:00:00'));

        $this->assertTrue(TimeOverlap::contains($needle->reveal(), $haystack->reveal()));
    }

    public function testNotContain()
    {
        $needle = $this->prophesize(TimeRangeInterface::class);
        $needle->getBegin()->willReturn(new \DateTime('2017-11-20 07:00:00'));
        $needle->getEnd()->willReturn(new \DateTime('2017-11-20 09:00:00'));

        $haystack = $this->prophesize(TimeRangeInterface::class);
        $haystack->getBegin()->willReturn(new \DateTime('2017-11-20 08:00:00'));
        $haystack->getEnd()->willReturn(new \DateTime('2017-11-20 20:00:00'));

        $this->assertFalse(TimeOverlap::contains($needle->reveal(), $haystack->reveal()));
    }
}
