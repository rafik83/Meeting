<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Tip\Event;

use Proximum\Vimeet\Application\Query\Tip\Event\TipListViewQuery;
use Proximum\Vimeet\Application\Query\Tip\Event\TipListViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\Event\TipView;
use Proximum\Vimeet\Application\View\Tip\TipListView;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class TipListViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event         = EventFactory::createEvent('Le plus grand cabaret du monde');
        $type          = new Type($event);
        $dateTime      = new \DateTime();
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $tip1 = new Tip('tip_1', false, true, false, $dateTime);
        $tip2 = new Tip('tip_2', false, false, true, $dateTime);
        $tip3 = new Tip('tip_3', true, false, true, $dateTime);
        $tips = [$tip1, $tip2, $tip3];

        foreach ($tips as $tip) {
            $tip->setType($type);
        }

        $expectedTipListView = new TipListView();
        $expectedTipListView->tipListView = [
            new TipView(null, 'tip_1', ['' => $type], ['admin.tip.column.visible.catalog']),
            new TipView(null, 'tip_2', ['' => $type], ['admin.tip.column.visible.print_planning']),
            new TipView(null, 'tip_3', ['' => $type], ['admin.tip.column.visible.meeting_management', 'admin.tip.column.visible.print_planning']),
        ];

        $expectedTipListView->results = [$tip1, $tip2, $tip3];

        $query = new TipListViewQuery($event, 1, 20);

        $tipRepository->paginateByEvent($event, 1, 20)->shouldBeCalled()->willReturn($tips);

        $handler = new TipListViewQueryHandler($tipRepository->reveal());
        $tipListView = $handler->handle($query);

        $this->assertEquals($expectedTipListView, $tipListView);
    }
}
