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
use Proximum\Vimeet\Application\View\Tip\TipListView;
use Proximum\Vimeet\Application\View\Tip\TipView;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $dateTime = new \DateTime();

        $tips = [
            new Tip('tip_1', true, true, true, $dateTime),
            new Tip('tip_2', false, false, true, $dateTime),
            new Tip('tip_3', true, false, true, $dateTime),
        ];

        $expectedTipListView = new TipListView();
        $expectedTipListView->tipListView = [
            new TipView(null, 'tip_1', [
                'admin.tip.column.visible.meeting_management',
                'admin.tip.column.visible.catalog',
                'admin.tip.column.visible.print_planning',
            ]),
            new TipView(null, 'tip_2', [
                'admin.tip.column.visible.print_planning'
            ]),
            new TipView(null, 'tip_3', [
                'admin.tip.column.visible.catalog',
                'admin.tip.column.visible.print_planning',
            ]),
        ];
        $expectedTipListView->results = [
            $tips[0],
            $tips[1],
            $tips[2],
        ];

        $query = new TipViewQuery(1);

        $tipRepository->paginate(1)->shouldBeCalled()->willReturn($tips);

        $handler = new TipViewQueryHandler($tipRepository->reveal());
        $tipListView = $handler->handle($query);

        $this->assertEquals($expectedTipListView, $tipListView);
    }
}
