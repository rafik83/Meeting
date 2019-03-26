<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Sheet\LinkedSheets\Admin\LinkedSheetsListView;
use Proximum\Vimeet\Application\Query\Sheet\LinkedSheets\Admin\LinkedSheetsListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\LinkedSheets\Admin\LinkedSheetsListViewQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\LinkedSheets\Admin\LinkedSheetsView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;

class LinkedSheetsListViewQueryHandlerTest extends TestCase
{
    public function test()
    {
        $linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $event = $this->prophesize(Event::class);

        $linkedSheets1CreatedAt = \DateTime::createFromFormat('!Y-m-d H:i', '2019-01-20 13:45');
        $linkedSheets2CreatedAt = \DateTime::createFromFormat('!Y-m-d H:i', '2019-02-14 17:38');

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getTitle()->shouldBeCalled()->willReturn('Namco');
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getTitle()->shouldBeCalled()->willReturn('Bandai');

        $linkedSheets1 = $this->prophesize(LinkedSheets::class);
        $linkedSheets1->getCreatedAt()->shouldBeCalled()->willReturn($linkedSheets1CreatedAt);
        $linkedSheets1->getSheets()->shouldBeCalled()->willReturn([$sheet1]);
        $linkedSheets2 = $this->prophesize(LinkedSheets::class);
        $linkedSheets2->getCreatedAt()->shouldBeCalled()->willReturn($linkedSheets2CreatedAt);
        $linkedSheets2->getSheets()->shouldBeCalled()->willReturn([$sheet2]);

        $linkedSheetsRepository->getByEvent($event->reveal())->willReturn(
            [$linkedSheets1->reveal(), $linkedSheets2->reveal()]
        );

        $handler = new LinkedSheetsListViewQueryHandler($linkedSheetsRepository->reveal());
        $linkedSheetsListViewQuery = new LinkedSheetsListViewQuery($event->reveal());

        $result = $handler->handle($linkedSheetsListViewQuery);

        $linkedSheetsView1 = new LinkedSheetsView(['Namco'], $linkedSheets1CreatedAt);
        $linkedSheetsView2 = new LinkedSheetsView(['Bandai'], $linkedSheets2CreatedAt);
        $expected = new LinkedSheetsListView([$linkedSheetsView1, $linkedSheetsView2]);

        $this->assertEquals($expected, $result);
    }
}
