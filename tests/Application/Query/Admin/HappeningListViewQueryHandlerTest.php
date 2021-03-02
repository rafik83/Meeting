<?php

namespace Proximum\Vimeet\Tests\Application\Query\Admin;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningViewQuery;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningListViewQuery;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningListViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningViewQueryHandler;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningDayView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningView;

class HappeningListViewQueryHandlerTest extends TestCase
{
    public function testShouldReturnOneHappeningDayViewIfAllHappeningOccursTheSameDay()
    {

        $event = $this->prophesize(Event::class);
        $locale = "fr";

        $happening1BeginDate =   new DateTime('2011-01-01T15:03:01.012345Z');
        $happening1 = $this->prophesize(Happening::class);
        $happening1->getBegin()->willReturn($happening1BeginDate);

        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningRepository->findListByEvent($event->reveal(), $locale)->willReturn([$happening1->reveal()]);


        $happeningView1 = $this->prophesize(HappeningView::class);
        $happeningHandler = $this->prophesize(HappeningViewQueryHandler::class);

        $happeningViewQuery1 = new HappeningViewQuery($happening1->reveal(), $locale);
        $happeningHandler->handle($happeningViewQuery1)->willReturn($happeningView1->reveal());

        $handler = new HappeningListViewQueryHandler($happeningRepository->reveal(), $happeningHandler->reveal());

        $query = new HappeningListViewQuery($event->reveal(), $locale);

        $result = $handler->handle($query);

        $happeningDayView1 =   new HappeningDayView($happening1BeginDate, [
            $happening1->reveal(),
        ]);

        $happeningDayView1->happeningListView = [
            $happeningView1->reveal()

        ];

        $expectedResult = [
            $happeningDayView1
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testShouldReturnAnHappeningDayViewByHappeningDate()
    {
        $event = $this->prophesize(Event::class);
        $locale = "fr";

        $happening1BeginDate =   new DateTime('2011-01-01T15:03:01.012345Z');
        $happening1 = $this->prophesize(Happening::class);
        $happening1->getBegin()->willReturn($happening1BeginDate);


        $happening2BeginDate =   new DateTime('2012-02-02T15:03:01.012345Z');
        $happening2 = $this->prophesize(Happening::class);
        $happening2->getBegin()->willReturn($happening2BeginDate);

        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningRepository->findListByEvent($event->reveal(), $locale)->willReturn([$happening1->reveal(), $happening2->reveal()]);


        $happeningView1 = $this->prophesize(HappeningView::class);
        $happeningHandler = $this->prophesize(HappeningViewQueryHandler::class);

        $happeningViewQuery1 = new HappeningViewQuery($happening1->reveal(), $locale);
        $happeningHandler->handle($happeningViewQuery1)->willReturn($happeningView1->reveal());

        $happeningView2 = $this->prophesize(HappeningView::class);
        $happeningViewQuery2 = new HappeningViewQuery($happening2->reveal(), $locale);
        $happeningHandler->handle($happeningViewQuery2)->willReturn($happeningView2->reveal());

        $handler = new HappeningListViewQueryHandler($happeningRepository->reveal(), $happeningHandler->reveal());

        $query = new HappeningListViewQuery($event->reveal(), $locale);

        $result = $handler->handle($query);

        $happeningDayView1 =   new HappeningDayView($happening1BeginDate, [
            $happening1->reveal(),
        ]);

        $happeningDayView1->happeningListView = [
            $happeningView1->reveal()

        ];

        $happeningDayView2 =   new HappeningDayView($happening2BeginDate, [
            $happening2->reveal(),
        ]);

        $happeningDayView2->happeningListView = [
            $happeningView2->reveal()

        ];

        $expectedResult = [
            $happeningDayView1,
            $happeningDayView2
        ];

        $this->assertEquals($expectedResult, $result);
    }
}
