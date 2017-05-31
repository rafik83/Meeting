<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Tip\Event;

use Proximum\Vimeet\Application\Query\Tip\Event\PaginateTipViewQuery;
use Proximum\Vimeet\Application\Query\Tip\Event\PaginateTipViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\PaginatedTipView;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class PaginateTipViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
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

        $expectedTipListView = new PaginatedTipView();
        $expectedTipListView->results = [$tip1, $tip2, $tip3];

        $query = new PaginateTipViewQuery($event, 1, 20);

        $tipRepository->paginateByEvent($event, 1, 20)->shouldBeCalled()->willReturn($tips);

        $handler = new PaginateTipViewQueryHandler($tipRepository->reveal());
        $tipListView = $handler->handle($query);

        $this->assertEquals($expectedTipListView, $tipListView);
    }
}
