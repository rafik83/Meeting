<?php

namespace Proximum\Vimeet\Tests\Application\Query\Analytic\MeetingSolution\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot\SpotSatisfactionViewQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot\SpotSatisfactionViewQueryHandler;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionView;
use Proximum\Vimeet\Domain\Model\Spot;

class SpotSatisfactionViewQueryHandlerTest extends TestCase
{
    /**
     * @dataProvider provideData
     *
     * @param int                  $slotAvailable
     * @param int                  $meeting
     * @param array                $unavailability
     * @param SpotSatisfactionView $expected
     */
    public function testHandle(int $slotAvailable, int $meeting, array $unavailability, SpotSatisfactionView $expected)
    {
        $spot = $this->prophesize(Spot::class);
        $spot->getId()->willReturn(1);
        $spot->getReference()->willReturn('ref');
        $spot->isVisio()->willReturn(true);
        $spot->hasSheets()->willReturn(false);
        $spot->getMeetingCapacity()->willReturn(2);
        $spot->getPriority()->willReturn(8);
        $spot->getSpotUnavailabilities()->willReturn($unavailability);

        $handler = new SpotSatisfactionViewQueryHandler();
        $result = $handler->handle(new SpotSatisfactionViewQuery(
            $spot->reveal(),
            $meeting,
            $slotAvailable
        ));

        $this->assertEquals($expected, $result);
    }

    public function provideData()
    {
        return [
            [
                10,
                8,
                [],
                new SpotSatisfactionView(1, 'ref', true, true, 8, 40),
            ],
            [
                7,
                10,
                [1, 2],
                new SpotSatisfactionView(1, 'ref', true, true, 8, 100),
            ],
            [
                7,
                0,
                [1, 2, 3],
                new SpotSatisfactionView(1, 'ref', true, true, 8, 0),
            ],
            [
                0,
                10,
                [],
                new SpotSatisfactionView(1, 'ref', true, true, 8, 500),
            ],
        ];
    }
}
