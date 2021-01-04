<?php

namespace Application\Query\Tip\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Tip\Event\PaginatedTipViewQuery;
use Proximum\Vimeet\Application\Query\Tip\Event\PaginatedTipViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\PaginatedTipView;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class PaginatedTipViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event         = EventFactory::createEvent('Le plus grand cabaret du monde');
        $type          = new Type($event);
        $dateTime      = new \DateTime();
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $tip1 = new Tip('tip_1', null, false, true, false, false, true, false, false, false, false, false, $dateTime);
        $tip2 = new Tip('tip_2', null, false, false, true, false, true, false, false,  false, false, false, $dateTime);
        $tip3 = new Tip('tip_3', null, true, false, true, false, true, false, false,  false, false, false, $dateTime);
        $tips = [$tip1, $tip2, $tip3];

        foreach ($tips as $tip) {
            $tip->setType($type);
        }

        $results = new PaginatedResult([$tip1, $tip2, $tip3], 1, 10, 3);
        $expectedTipListView = new PaginatedTipView($results);

        $query = new PaginatedTipViewQuery($event, 1, 20);

        $tipRepository->paginateByEvent($event, 1, 20)->shouldBeCalled()->willReturn($results);

        $handler = new PaginatedTipViewQueryHandler($tipRepository->reveal());
        $tipListView = $handler->handle($query);

        $this->assertEquals($expectedTipListView, $tipListView);
    }
}
