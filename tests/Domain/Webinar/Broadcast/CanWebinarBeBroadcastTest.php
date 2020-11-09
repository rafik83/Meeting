<?php

namespace Proximum\Vimeet\Tests\Domain\Webinar\Broadcast;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Happening\Webinar\Broadcast\CanWebinarBeBroadcast;
use Proximum\Vimeet\Domain\Model\Happening;

class CanWebinarBeBroadcastTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            ['2002-03-21 12:00', '2002-03-21 13:00', '2002-03-21 12:30', true],
            ['2002-03-21 12:00', '2002-03-21 13:00', '2002-03-21 13:29', true],
            ['2002-03-21 12:00', '2002-03-21 13:00', '2002-03-21 11:41', true],
            ['2002-03-21 12:00', '2002-03-21 13:00', '2002-03-21 14:30', false],
            ['2002-03-21 12:00', '2002-03-21 13:00', '2002-03-21 10:30', false],
            ['2002-03-21 12:00', '2002-03-22 12:00', '2002-03-21 12:30', false],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testValidHours(string $begin, string $end, string $now, bool $isValid)
    {
        $happening = $this->prophesize(Happening::class);
        $happening->isWebinar()->willReturn(true);
        $happening->allowWebinarOnHLS()->willReturn(true);
        $happening->getWebinarSessionId()->willReturn('aaa-bbb-ccc-ddd');
        $happening->getBegin()->willReturn(DateTime::createFromFormat('!Y-m-d H:i', $begin));
        $happening->getEnd()->willReturn(DateTime::createFromFormat('!Y-m-d H:i', $end));

        $canWebinarBeBroadcast = new CanWebinarBeBroadcast(DateTime::createFromFormat('!Y-m-d H:i', $now));
        self::assertEquals($isValid, $canWebinarBeBroadcast($happening->reveal()));
    }

    public function testHappeningIsNotWebinar()
    {
        $happening = $this->prophesize(Happening::class);
        $happening->isWebinar()->shouldBeCalled()->willReturn(false);

        $canWebinarBeBroadcast = new CanWebinarBeBroadcast(DateTime::createFromFormat('!Y-m-d H:i', '2002-03-21 12:30'));
        self::assertFalse($canWebinarBeBroadcast($happening->reveal()));
    }

    public function testWebinarNotAllowedOnHls()
    {
        $happening = $this->prophesize(Happening::class);
        $happening->isWebinar()->willReturn(true);
        $happening->allowWebinarOnHLS()->shouldBeCalled()->willReturn(false);

        $canWebinarBeBroadcast = new CanWebinarBeBroadcast(DateTime::createFromFormat('!Y-m-d H:i', '2002-03-21 12:30'));
        self::assertFalse($canWebinarBeBroadcast($happening->reveal()));
    }

    public function testWebinaHasNoSessionId()
    {
        $happening = $this->prophesize(Happening::class);
        $happening->isWebinar()->willReturn(true);
        $happening->allowWebinarOnHLS()->willReturn(true);
        $happening->getWebinarSessionId()->shouldBeCalled()->willReturn('');

        $canWebinarBeBroadcast = new CanWebinarBeBroadcast(DateTime::createFromFormat('!Y-m-d H:i', '2002-03-21 12:30'));
        self::assertFalse($canWebinarBeBroadcast($happening->reveal()));
    }
}
