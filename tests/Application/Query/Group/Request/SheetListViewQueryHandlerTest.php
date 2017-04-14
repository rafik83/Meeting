<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Group\Request;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Group\Request\RequestViewQuery;
use Proximum\Vimeet\Application\Query\Group\Request\RequestViewQueryHandler;
use Proximum\Vimeet\Application\Query\Group\Request\SheetListViewQuery;
use Proximum\Vimeet\Application\Query\Group\Request\SheetListViewQueryHandler;
use Proximum\Vimeet\Application\Query\Group\Request\SheetViewQuery;
use Proximum\Vimeet\Application\Query\Group\Request\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\Group\Request\RequestView;
use Proximum\Vimeet\Application\View\Group\Request\SheetListView;
use Proximum\Vimeet\Application\View\Group\Request\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetListViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event  = $this->prophesize(Event::class);
        $group  = $this->prophesize(Group::class);
        $group->getId()->willReturn(9);
        $group->getTitle()->willReturn('group title');
        $group->getEvent()->willReturn($event->reveal());

        $groupSheet1 = $this->prophesize(Sheet::class);
        $groupSheet2 = $this->prophesize(Sheet::class);

        $sheetMet1 = $this->prophesize(Sheet::class);
        $sheetMet2 = $this->prophesize(Sheet::class);
        $sheetMet4 = $this->prophesize(Sheet::class);
        $sheetMet5 = $this->prophesize(Sheet::class);

        $groupSheets = [
            1 => $groupSheet1->reveal(),
            2 => $groupSheet2->reveal(),
        ];

        $sheetsMet = [
            $sheetMet1,
            $sheetMet2,
            $groupSheet1,
            $sheetMet4,
            $sheetMet5,
        ];

        $groupSheet1->getId()->willReturn(1);
        $groupSheet2->getId()->willReturn(2);
        $sheetMet1->getId()->willReturn(3);
        $sheetMet2->getId()->willReturn(4);
        $sheetMet4->getId()->willReturn(5);
        $sheetMet5->getId()->willReturn(6);

        $locale = 'fr';
        $page   = 1;
        $limit  = 2; // To ease test
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesserCache::class);
        $sheetInfoGuesser->guessSheetTitle($sheetMet1, $locale)->shouldBeCalled()->willReturn('A 1');
        $sheetInfoGuesser->guessSheetTitle($sheetMet2, $locale)->shouldBeCalled()->willReturn('Z 2');
        $sheetInfoGuesser->guessSheetTitle($groupSheet1, $locale)->shouldBeCalled()->willReturn('B 3');
        $sheetInfoGuesser->guessSheetTitle($sheetMet4, $locale)->shouldBeCalled()->willReturn('C 4');
        $sheetInfoGuesser->guessSheetTitle($sheetMet5, $locale)->shouldBeCalled()->willReturn('D 5');
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $sheetViewQueryHandler = $this->prophesize(SheetViewQueryHandler::class);
        $requestViewQueryHandler = $this->prophesize(RequestViewQueryHandler::class);

        $sheetRepository->getByGroup($group->reveal())->shouldBeCalled()->willReturn($groupSheets);
        $sheetRepository
            ->getSheetsMetBySheets($event->reveal(), $groupSheets)
            ->shouldBeCalled()
            ->willReturn($sheetsMet)
        ;

        $request1 = $this->prophesize(Request::class);
        $request1->getFromSheet()->willReturn($sheetMet1->reveal());
        $request1->getToSheet()->willReturn($groupSheet1->reveal());
        $request2 = $this->prophesize(Request::class);
        $request2->getFromSheet()->willReturn($sheetMet1->reveal());
        $request2->getToSheet()->willReturn($groupSheet2->reveal());
        $request3 = $this->prophesize(Request::class);
        $request3->getFromSheet()->willReturn($groupSheet1->reveal());
        $request3->getToSheet()->willReturn($groupSheet2->reveal());

        $requestRepository
            ->getRequestsOfSheetsWithSheets(
                $event->reveal(), [$sheetMet1->reveal(), $groupSheet1->reveal()], $groupSheets
            )->shouldBeCalled()
            ->willReturn([
                $request1->reveal(),
                $request2->reveal(),
                $request3->reveal(),
            ])
        ;

        $requestView1 = $this->prophesize(RequestView::class);
        $requestView2 = $this->prophesize(RequestView::class);
        $requestView3 = $this->prophesize(RequestView::class);

        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheetMet1->reveal(), $request1->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView1);
        $requestViewQueryHandler
            ->handle(new RequestViewQuery($sheetMet1->reveal(), $request2->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView2);
        $requestViewQueryHandler
            ->handle(new RequestViewQuery($groupSheet1->reveal(), $request3->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($requestView3);

        $sheetView1 = new SheetView(1, 'title 1', $sheetMet1->reveal());
        $sheetView2 = new SheetView(2, 'title 2', $groupSheet1->reveal());

        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheetMet1->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView1);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($groupSheet1->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn($sheetView2);


        $expectedSheetView1 = new SheetView(1, 'title 1', $sheetMet1->reveal());
        $expectedSheetView1->addRequest($requestView1->reveal());
        $expectedSheetView1->addRequest($requestView2->reveal());
        $expectedSheetView2 = new SheetView(2, 'title 2', $groupSheet1->reveal());
        $expectedSheetView2->addRequest($requestView3->reveal());

        $handler = new SheetListViewQueryHandler(
            $sheetRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $requestRepository->reveal(),
            $sheetViewQueryHandler->reveal(),
            $requestViewQueryHandler->reveal()
        );

        $result = $handler->handle(new SheetListViewQuery($group->reveal(), $locale, $page, $limit));

        $expected = new SheetListView(9, 'group title', [3 => $expectedSheetView1, 1 => $expectedSheetView2], 1, 3);

        $this->assertEquals($expected, $result);
    }
}
