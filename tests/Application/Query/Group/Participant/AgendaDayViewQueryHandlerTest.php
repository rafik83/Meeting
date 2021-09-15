<?php

namespace Proximum\Vimeet\Tests\Application\Query\Group\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Group\Participant\AgendaDayViewQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\AgendaDayViewQueryHandler;
use Proximum\Vimeet\Application\Query\Group\Participant\SlotViewsQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\SlotViewsQueryHandler;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AgendaDayViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $day   = new Day($event, new \DateTime(), new \DateTime());

        $slotViewsQuery = new SlotViewsQuery($day);
        $slotViewsQueryHandler = $this->prophesize(SlotViewsQueryHandler::class);
        $slotViewsQueryHandler->handle($slotViewsQuery)->shouldBeCalled()->willReturn([]);

        (new AgendaDayViewQueryHandler($slotViewsQueryHandler->reveal()))->handle(new AgendaDayViewQuery($day));
    }
}
