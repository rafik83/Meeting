<?php

namespace Proximum\Vimeet\Tests\Domain\Happening;

use Proximum\Vimeet\Domain\Happening\HappeningsNotOverlapped;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Happening;

class HappeningsNotOverlappedTest extends TestCase
{
    public function testGetHappeningsNotOverlapped()
    {
        $happening1 = $this->prophesize(Happening::class);
        $happening1->getId()->willReturn(1);
        $happening1->getBegin()->willReturn(new \DateTime('2018-05-09 10:00:00'));
        $happening1->getEnd()->willReturn(new \DateTime('2018-05-09 11:00:00'));

        $happening2 = $this->prophesize(Happening::class);
        $happening2->getId()->willReturn(2);
        $happening2->getBegin()->willReturn(new \DateTime('2018-05-09 11:00:00'));
        $happening2->getEnd()->willReturn(new \DateTime('2018-05-09 11:30:00'));

        $happening3 = $this->prophesize(Happening::class);
        $happening3->getId()->willReturn(3);
        $happening3->getBegin()->willReturn(new \DateTime('2018-05-09 10:30:00'));
        $happening3->getEnd()->willReturn(new \DateTime('2018-05-09 11:00:00'));

        $happening4 = $this->prophesize(Happening::class);
        $happening4->getId()->willReturn(4);
        $happening4->getBegin()->willReturn(new \DateTime('2018-05-09 09:45:00'));
        $happening4->getEnd()->willReturn(new \DateTime('2018-05-09 10:05:00'));

        $happening5 = $this->prophesize(Happening::class);
        $happening5->getId()->willReturn(5);
        $happening5->getBegin()->willReturn(new \DateTime('2018-05-10 09:45:00'));
        $happening5->getEnd()->willReturn(new \DateTime('2018-05-10 10:05:00'));

        $happeningsNotOverlapped = new HappeningsNotOverlapped();

        $this->assertEquals(
            [
                $happening2->reveal(),
                $happening5->reveal(),
            ],
            $happeningsNotOverlapped->getHappeningsNotOverlapped(
                [
                    $happening1->reveal(),
                    $happening2->reveal(),
                    $happening3->reveal(),
                    $happening4->reveal(),
                    $happening5->reveal(),
                ]
            )
        );

        $this->assertEquals(
            [],
            $happeningsNotOverlapped->getHappeningsNotOverlapped([])
        );
    }
}
