<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Admin\Spot;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\AgendaSpotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\AgendaSpotViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\DaySpotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\DaySpotViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaDayView;
use Proximum\Vimeet\Application\View\Agenda\AgendaSpotView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AgendaSpotViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event     = EventFactory::createEvent();
        $spot      = $this->prophesize(Spot::class);
        $startTime = new \DateTime();
        $endTime   = new \DateTime();
        $day       = new Day($event, $startTime, $endTime);

        $spot->getId()->willReturn(1);
        $spot->getReference()->willReturn('A01');
        $spot->getEvent()->willReturn($event);

        $locale = 'fr';

        // Mock
        $dayView                 = $this->prophesize(AgendaDayView::class);
        $dayRepository           = $this->prophesize(DayRepositoryInterface::class);
        $daySpotViewQueryHandler = $this->prophesize(DaySpotViewQueryHandler::class);

        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([$day]);

        $daySpotViewQueryHandler->handle(Argument::type(DaySpotViewQuery::class))->shouldBeCalled()
            ->willReturn($dayView->reveal());

        // Expected
        $expectedAgendaSpotView = new AgendaSpotView(
            1,
            'A01',
            [$dayView->reveal()]
        );

        $query   = new AgendaSpotViewQuery($spot->reveal(), $event, $locale);
        $handler = new AgendaSpotViewQueryHandler(
            $dayRepository->reveal(),
            $daySpotViewQueryHandler->reveal()
        );

        $agendaSpotView = $handler->handle($query);

        $this->assertEquals($expectedAgendaSpotView, $agendaSpotView);
    }
}
