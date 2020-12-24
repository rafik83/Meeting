<?php

namespace Proximum\Vimeet\Tests\Application\Query\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Spot\SpotUnavailabilityQuery;
use Proximum\Vimeet\Application\Query\Spot\SpotUnavailabilityQueryHandler;
use Proximum\Vimeet\Application\View\Spot\SpotUnavailabilityView;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SpotUnavailabilityViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $spotIds = [2, 6, 9];
        $spots = [
            new Spot('REF1', $event, 15.0, 1, 1, true),
            new Spot('REF2', $event, 12.0, 1, 1, true),
            new Spot('REF3', $event, 14.0, 1, 1, true),
        ];

        // Mock
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);

        $spotRepository->findMany($event, $spotIds)->shouldBeCalled()->willReturn($spots);

        $handler = new SpotUnavailabilityQueryHandler(
            $spotRepository->reveal()
        );

        $view = $handler->handle(new SpotUnavailabilityQuery($event, $spotIds));

        $this->assertInstanceOf(SpotUnavailabilityView::class, $view);
    }
}
