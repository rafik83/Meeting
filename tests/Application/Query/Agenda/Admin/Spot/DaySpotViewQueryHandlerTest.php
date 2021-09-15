<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Admin\Spot;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\DaySpotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\DaySpotViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\SlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\SlotViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaDayView;
use Proximum\Vimeet\Application\View\Agenda\Slot\SpotSlotView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SpotFactory;

class DaySpotViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event     = EventFactory::createEvent();
        $startTime = new \DateTime();
        $endTime   = new \DateTime();
        $dayNumber = 1;
        $day       = new Day($event, $startTime, $endTime);
        $spot      = SpotFactory::create($event, 'A01');
        $locale    = 'fr';

        // Mock
        $slotView = $this->prophesize(SpotSlotView::class);
        $slotViewQueryHandler = $this->prophesize(SlotViewQueryHandler::class);

        $slotViewQueryHandler
            ->handle(Argument::type(SlotViewQuery::class))
            ->shouldBeCalled()
            ->willReturn([$slotView->reveal()]);

        // Expected
        $expectedAgendaDayView = new AgendaDayView(
            1,
            [$slotView->reveal()]
        );

        $query   = new DaySpotViewQuery($day, $dayNumber, $event, $spot, $locale);
        $handler = new DaySpotViewQueryHandler($slotViewQueryHandler->reveal());

        $agendaDayView = $handler->handle($query);

        $this->assertEquals($expectedAgendaDayView, $agendaDayView);
    }
}
