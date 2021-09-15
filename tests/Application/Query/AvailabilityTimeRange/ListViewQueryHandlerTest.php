<?php

namespace Proximum\Vimeet\Tests\Application\Query\AvailabilityTimeRange;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\AvailabilityTimeRange\AvailabilityTimeRangeViewQuery;
use Proximum\Vimeet\Application\Query\AvailabilityTimeRange\AvailabilityTimeRangeViewQueryHandler;
use Proximum\Vimeet\Application\Query\AvailabilityTimeRange\ListViewQuery;
use Proximum\Vimeet\Application\Query\AvailabilityTimeRange\ListViewQueryHandler;
use Proximum\Vimeet\Application\View\AvailabilityTimeRange\AvailabilityTimeRangeView;
use Proximum\Vimeet\Application\View\AvailabilityTimeRange\ListView;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;

class ListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $begin1 = new \DateTime('2017-10-10 10:00:00.000');
        $begin2 = new \DateTime('2017-10-10 13:00:00.000');
        $begin3 = new \DateTime('2017-10-11 10:00:00.000');
        $end1 = new \DateTime('2017-10-10 12:00:00.000');
        $end2 = new \DateTime('2017-10-10 17:00:00.000');
        $end3 = new \DateTime('2017-10-11 18:00:00.000');

        $event = $this->prophesize(Event::class);
        $availabilityTimeRange1 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange2 = $this->prophesize(AvailabilityTimeRange::class);
        $availabilityTimeRange3 = $this->prophesize(AvailabilityTimeRange::class);

        $view1 = new AvailabilityTimeRangeView('name1', $begin1, $end1, []);
        $view2 = new AvailabilityTimeRangeView('name2', $begin2, $end2, []);
        $view3 = new AvailabilityTimeRangeView('name3', $begin3, $end3, []);

        $availabilityTimeRangeRepository = $this->prophesize(AvailabilityTimeRangeRepositoryInterface::class);
        $availabilityTimeRangeRepository
            ->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$availabilityTimeRange1->reveal(), $availabilityTimeRange2->reveal(), $availabilityTimeRange3->reveal()])
        ;

        $availabilityTimeRangeViewQueryHandler = $this->prophesize(AvailabilityTimeRangeViewQueryHandler::class);
        $availabilityTimeRangeViewQueryHandler
            ->handle(new AvailabilityTimeRangeViewQuery($availabilityTimeRange1->reveal()))
            ->shouldBeCalled()
            ->willReturn($view1)
        ;

        $availabilityTimeRangeViewQueryHandler
            ->handle(new AvailabilityTimeRangeViewQuery($availabilityTimeRange2->reveal()))
            ->shouldBeCalled()
            ->willReturn($view2)
        ;
        $availabilityTimeRangeViewQueryHandler
            ->handle(new AvailabilityTimeRangeViewQuery($availabilityTimeRange3->reveal()))
            ->shouldBeCalled()
            ->willReturn($view3)
        ;

        $handler = new ListViewQueryHandler(
            $availabilityTimeRangeRepository->reveal(),
            $availabilityTimeRangeViewQueryHandler->reveal()
        );
        $result = $handler->handle(new ListViewQuery($event->reveal()));

        $expectedViews = [$view1, $view2, $view3];
        $expected = new ListView($expectedViews);

        $this->assertEquals($expected, $result);
    }
}
