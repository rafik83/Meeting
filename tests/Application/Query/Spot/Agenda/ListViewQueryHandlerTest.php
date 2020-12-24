<?php

namespace Proximum\Vimeet\Tests\Application\Query\Spot\Agenda;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Query\Spot\Agenda\ListViewQuery;
use Proximum\Vimeet\Application\Query\Spot\Agenda\ListViewQueryHandler;
use Proximum\Vimeet\Application\Query\Spot\Agenda\SpotViewQuery;
use Proximum\Vimeet\Application\Query\Spot\Agenda\SpotViewQueryHandler;
use Proximum\Vimeet\Application\View\Spot\Agenda\ListView;
use Proximum\Vimeet\Application\View\Spot\Agenda\SpotView;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();

        // Mock
        $spotRepository       = $this->prophesize(SpotRepositoryInterface::class);
        $spotViewQueryHandler = $this->prophesize(SpotViewQueryHandler::class);

        $spot = new Spot('A01', $event, 20.0, 2, 6, true);
        $spotView = new SpotView(null, 'A01', false);

        $spotRepository->getActiveByEvent($event)->shouldBeCalled()->willReturn([$spot]);
        $spotViewQueryHandler->handle(Argument::type(SpotViewQuery::class))->willReturn($spotView);

        $query   = new ListViewQuery($event);
        $handler = new ListViewQueryHandler(
            $spotRepository->reveal(),
            $spotViewQueryHandler->reveal()
        );

        $listView = $handler->handle($query);

        $this->assertInstanceOf(ListView::class, $listView);
        $this->assertEquals($listView->spotViews, [$spotView]);
    }

    public function testInactiveHandle()
    {
        $event = EventFactory::createEvent();

        // Mock
        $spotRepository       = $this->prophesize(SpotRepositoryInterface::class);
        $spotViewQueryHandler = $this->prophesize(SpotViewQueryHandler::class);

        $spotRepository->getActiveByEvent($event)->shouldBeCalled()->willReturn([]);
        $spotViewQueryHandler->handle(Argument::type(SpotViewQuery::class))->shouldNotBeCalled();

        $query   = new ListViewQuery($event);
        $handler = new ListViewQueryHandler(
            $spotRepository->reveal(),
            $spotViewQueryHandler->reveal()
        );

        $listView = $handler->handle($query);

        $this->assertInstanceOf(ListView::class, $listView);
        $this->assertEquals($listView->spotViews, []);
    }
}
