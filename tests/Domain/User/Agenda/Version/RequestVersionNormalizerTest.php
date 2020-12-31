<?php

namespace Proximum\Vimeet\Tests\Domain\User\Agenda\Version;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\User\Agenda\Version\RequestVersionNormalizer;

class RequestVersionNormalizerTest extends TestCase
{
    public function testNormalizeInvalidRequest()
    {
        $this->expectException(\InvalidArgumentException::class);
        $request = $this->prophesize(Request::class);
        $request->hasMeeting()->willReturn(false);

        $requestVersionNormalizer = new RequestVersionNormalizer();
        $requestVersionNormalizer->normalize($request->reveal());
    }

    public function testNormalize()
    {
        $request = $this->prophesize(Request::class);
        $meeting = $this->prophesize(Meeting::class);
        $fromSheet = $this->prophesize(Sheet::class);
        $toSheet = $this->prophesize(Sheet::class);
        $spot = $this->prophesize(Spot::class);
        $slot = $this->prophesize(MeetingSlot::class);
        $request->getId()->willReturn(1337);
        $request->hasMeeting()->willReturn(true);
        $request->getFromSheet()->willReturn($fromSheet->reveal());
        $request->getToSheet()->willReturn($toSheet->reveal());
        $request->getMeeting()->willReturn($meeting->reveal());
        $fromSheet->getId()->willReturn(123);
        $toSheet->getId()->willReturn(321);
        $spot->getId()->willReturn(9);
        $slot->getId()->willReturn(11);
        $meeting->getSlot()->willReturn($slot->reveal());
        $meeting->getSpot()->willReturn($spot->reveal());

        $requestVersionNormalizer = new RequestVersionNormalizer();
        $result = $requestVersionNormalizer->normalize($request->reveal());

        $expected = [
            'request'   => 1337,
            'fromSheet' => 123,
            'toSheet'   => 321,
            'slot'      => 11,
            'spot'      => 9,
        ];

        $this->assertEquals($expected, $result);
    }
}
