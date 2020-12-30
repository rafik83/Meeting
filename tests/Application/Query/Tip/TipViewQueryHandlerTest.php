<?php

namespace Proximum\Vimeet\Tests\Application\Query\Tip;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Tip\TipViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\PaginatedTipView;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $dateTime = new \DateTime();

        $tip1 = new Tip('tip_1', null, false, true, false, false, false, false, false, false, false, false, $dateTime);
        $tip2 = new Tip('tip_2', null, false, true, false, false, false, false, false, false, false, false, $dateTime);
        $tip3 = new Tip('tip_3', null, false, true, false, false, false, false, false, false, false, false, $dateTime);

        $tips = [$tip1, $tip2, $tip3];

        $results = new PaginatedResult($tips, 1, 10, 3);

        $expectedTipListView = new PaginatedTipView($results);

        $query = new TipViewQuery(1, 20);

        $tipRepository->paginate(1, 20)->shouldBeCalled()->willReturn($results);

        $handler = new TipViewQueryHandler($tipRepository->reveal());
        $tipListView = $handler->handle($query);

        $this->assertEquals($expectedTipListView, $tipListView);
    }
}
