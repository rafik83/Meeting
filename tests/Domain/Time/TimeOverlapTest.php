<?php

namespace Proximum\Vimeet\Tests\Domain\Time;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Time\TimeOverlap;
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

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

    public function testFloorReturnFloor()
    {
        $threshold = new \DateTime('2017-11-20 20:00:00');
        $datetime = new \DateTime('2017-11-20 21:00:00');

        $this->assertEquals($threshold, TimeOverlap::floor($threshold, $datetime));
    }

    public function testFloorReturnDatetime()
    {
        $threshold = new \DateTime('2017-11-20 20:00:00');
        $datetime = new \DateTime('2017-11-20 19:59:59');

        $this->assertEquals($datetime, TimeOverlap::floor($threshold, $datetime));
    }

    public function testCeilReturnCeil()
    {
        $ceiling = new \DateTime('2017-11-20 20:00:00');
        $datetime = new \DateTime('2017-11-20 19:00:00');

        $this->assertEquals($ceiling, TimeOverlap::ceil($ceiling, $datetime));
    }

    public function testCeilReturnDatetime()
    {
        $ceiling = new \DateTime('2017-11-20 20:00:00');
        $datetime = new \DateTime('2017-11-20 20:59:59');

        $this->assertEquals($datetime, TimeOverlap::ceil($ceiling, $datetime));
    }

    public function testBeginIn()
    {
        $needle = $this->prophesize(TimeRangeInterface::class);
        $needle->getBegin()->willReturn(new \DateTime('2017-11-20 09:00:00'));

        $haystack = $this->prophesize(TimeRangeInterface::class);
        $haystack->getBegin()->willReturn(new \DateTime('2017-11-20 08:55:00'));
        $haystack->getEnd()->willReturn(new \DateTime('2017-11-20 09:10:00'));

        $this->assertTrue(TimeOverlap::beginIn($needle->reveal(), $haystack->reveal()));
    }

    public function testBeginInWithSameBegin()
    {
        $needle = $this->prophesize(TimeRangeInterface::class);
        $needle->getBegin()->willReturn(new \DateTime('2017-11-20 09:00:00'));

        $haystack = $this->prophesize(TimeRangeInterface::class);
        $haystack->getBegin()->willReturn(new \DateTime('2017-11-20 09:00:00'));
        $haystack->getEnd()->willReturn(new \DateTime('2017-11-20 09:10:00'));

        $this->assertTrue(TimeOverlap::beginIn($needle->reveal(), $haystack->reveal()));
    }

    public function testNotBeginIn()
    {
        $needle = $this->prophesize(TimeRangeInterface::class);
        $needle->getBegin()->willReturn(new \DateTime('2017-11-20 09:00:00'));

        $haystack = $this->prophesize(TimeRangeInterface::class);
        $haystack->getBegin()->willReturn(new \DateTime('2017-11-20 10:55:00'));
        $haystack->getEnd()->willReturn(new \DateTime('2017-11-20 11:10:00'));

        $this->assertFalse(TimeOverlap::beginIn($needle->reveal(), $haystack->reveal()));
    }

    public function testEndIn()
    {
        $needle = $this->prophesize(TimeRangeInterface::class);
        $needle->getEnd()->willReturn(new \DateTime('2017-11-20 09:00:00'));

        $haystack = $this->prophesize(TimeRangeInterface::class);
        $haystack->getBegin()->willReturn(new \DateTime('2017-11-20 08:55:00'));
        $haystack->getEnd()->willReturn(new \DateTime('2017-11-20 09:10:00'));

        $this->assertTrue(TimeOverlap::endIn($needle->reveal(), $haystack->reveal()));
    }

    public function testEndInWithSameEnd()
    {
        $needle = $this->prophesize(TimeRangeInterface::class);
        $needle->getEnd()->willReturn(new \DateTime('2017-11-20 09:00:00'));

        $haystack = $this->prophesize(TimeRangeInterface::class);
        $haystack->getBegin()->willReturn(new \DateTime('2017-11-20 08:55:00'));
        $haystack->getEnd()->willReturn(new \DateTime('2017-11-20 09:00:00'));

        $this->assertTrue(TimeOverlap::endIn($needle->reveal(), $haystack->reveal()));
    }

    public function testNotEndIn()
    {
        $needle = $this->prophesize(TimeRangeInterface::class);
        $needle->getEnd()->willReturn(new \DateTime('2017-11-20 09:00:00'));

        $haystack = $this->prophesize(TimeRangeInterface::class);
        $haystack->getBegin()->willReturn(new \DateTime('2017-11-20 08:55:00'));
        $haystack->getEnd()->willReturn(new \DateTime('2017-11-20 08:59:00'));

        $this->assertFalse(TimeOverlap::endIn($needle->reveal(), $haystack->reveal()));
    }

    public function testNotOverlapBecauseItEndBefore()
    {
        $needle = $this->prophesize(TimeRangeInterface::class);
        $needle->getBegin()->willReturn(new \DateTime('2017-11-20 09:00:00'));
        $needle->getEnd()->willReturn(new \DateTime('2017-11-20 10:00:00'));

        $haystack = $this->prophesize(TimeRangeInterface::class);
        $haystack->getBegin()->willReturn(new \DateTime('2017-11-20 10:00:00'));
        $haystack->getEnd()->willReturn(new \DateTime('2017-11-20 11:00:00'));

        $this->assertFalse(TimeOverlap::overlap($needle->reveal(), $haystack->reveal()));
    }

    public function testNotOverlapBecauseItBeginAfter()
    {
        $needle = $this->prophesize(TimeRangeInterface::class);
        $needle->getBegin()->willReturn(new \DateTime('2017-11-20 09:00:00'));
        $needle->getEnd()->willReturn(new \DateTime('2017-11-20 09:59:59'));

        $haystack = $this->prophesize(TimeRangeInterface::class);
        $haystack->getBegin()->willReturn(new \DateTime('2017-11-20 08:00:00'));
        $haystack->getEnd()->willReturn(new \DateTime('2017-11-20 09:00:00'));

        $this->assertFalse(TimeOverlap::overlap($needle->reveal(), $haystack->reveal()));
    }

    public function testOverlapNeedleContainsHaystack()
    {
        $needle = $this->prophesize(TimeRangeInterface::class);
        $needle->getBegin()->willReturn(new \DateTime('2017-11-20 09:00:00'));
        $needle->getEnd()->willReturn(new \DateTime('2017-11-20 10:00:00'));

        $haystack = $this->prophesize(TimeRangeInterface::class);
        $haystack->getBegin()->willReturn(new \DateTime('2017-11-20 09:20:00'));
        $haystack->getEnd()->willReturn(new \DateTime('2017-11-20 09:40:00'));

        $this->assertTrue(TimeOverlap::overlap($needle->reveal(), $haystack->reveal()));
    }

    public function testTouch()
    {
        $this->assertTrue(
            TimeOverlap::touch(
                new TimeRangeView(new \DateTime('2017-11-20 09:00:00'), new \DateTime('2017-11-20 10:00:00')),
                new TimeRangeView(new \DateTime('2017-11-20 10:00:00'), new \DateTime('2017-11-20 11:00:00'))
            )
        );

        $this->assertTrue(
            TimeOverlap::touch(
                new TimeRangeView(new \DateTime('2017-11-20 10:00:00'), new \DateTime('2017-11-20 11:00:00')),
                new TimeRangeView(new \DateTime('2017-11-20 09:00:00'), new \DateTime('2017-11-20 10:00:00'))
            )
        );

        $this->assertFalse(
            TimeOverlap::touch(
                new TimeRangeView(new \DateTime('2017-11-20 10:00:00'), new \DateTime('2017-11-20 11:00:00')),
                new TimeRangeView(new \DateTime('2017-11-20 11:05:00'), new \DateTime('2017-11-20 12:00:00'))
            )
        );
    }
}
