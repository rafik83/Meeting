<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Tip;

use Proximum\Vimeet\Application\Query\Tip\TipViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\PaginatedTipView;
use Proximum\Vimeet\Application\View\Tip\TipView;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $dateTime = new \DateTime();

        $tips = [
            new Tip('tip_1', false, true, false, $dateTime),
            new Tip('tip_2', false, false, true, $dateTime),
            new Tip('tip_3', true, false, true, $dateTime),
        ];

        $results = new PaginatedResult([$tips], 1, 10, 3);

        $expectedTipListView = new PaginatedTipView($results);

        $query = new TipViewQuery(1, 20);

        $tipRepository->paginate(1, 20)->shouldBeCalled()->willReturn($results);

        $handler = new TipViewQueryHandler($tipRepository->reveal());
        $tipListView = $handler->handle($query);

        $this->assertEquals($expectedTipListView, $tipListView);
    }
}
