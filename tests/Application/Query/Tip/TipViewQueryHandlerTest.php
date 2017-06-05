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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class TipViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $dateTime = new \DateTime();
        $event1 = $this->prophesize(Event::class);
        $event1->getTitle()->shouldBeCalled()->willReturn("ZZZ");
        $event2 = $this->prophesize(Event::class);
        $event2->getTitle()->shouldBeCalled()->willReturn("AAA");

        $type1event1 = new Type($event1->reveal());
        $type2event1 = new Type($event1->reveal());
        $type3event1 = new Type($event1->reveal());

        $type1event2 = new Type($event2->reveal());
        $type2event2 = new Type($event2->reveal());

        $tip1 = new Tip('tip_1', false, true, false, false, false, false, $dateTime);
        $tip2 = new Tip('tip_2', false, true, false, false, false, false, $dateTime);
        $tip3 = new Tip('tip_3', false, true, false, false, false, false, $dateTime);

        $tip1->addType($type1event1);
        $tip1->addType($type2event1);
        $tip1->addType($type3event1);

        $tip2->addType($type1event1);
        $tip2->addType($type2event1);
        $tip2->addType($type3event1);
        $tip2->addType($type1event2);
        $tip2->addType($type2event2);

        $tips = [$tip1, $tip2, $tip3];

        $results = new PaginatedResult($tips, 1, 10, 3);

        $expectedTipListView = new PaginatedTipView($results);

        $query = new TipViewQuery(1, 20);

        $tipRepository->paginate(1, 20)->shouldBeCalled()->willReturn($results);

        $handler = new TipViewQueryHandler($tipRepository->reveal());
        $tipListView = $handler->handle($query);

        $secondTipEventsResult = $tipListView->results->results[1]->getUnduplicatedEvents();
        $this->assertCount(1, $tipListView->results->results[0]->getUnduplicatedEvents());
        $this->assertCount(2, $secondTipEventsResult);
        $this->assertEquals('AAA', reset($secondTipEventsResult)->getTitle());

        $this->assertEquals($expectedTipListView, $tipListView);
    }
}
