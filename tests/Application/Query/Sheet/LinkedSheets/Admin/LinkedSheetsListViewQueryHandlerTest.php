<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet\LinkedSheets\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Criteria\LinkedSheets\AreRemovableLinkedSheetsCriteria;
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
        // prepare data
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
        $linkedSheets1->getId()->shouldBeCalled()->willReturn(1);
        $linkedSheets2 = $this->prophesize(LinkedSheets::class);
        $linkedSheets2->getCreatedAt()->shouldBeCalled()->willReturn($linkedSheets2CreatedAt);
        $linkedSheets2->getSheets()->shouldBeCalled()->willReturn([$sheet2]);
        $linkedSheets2->getId()->shouldBeCalled()->willReturn(2);

        $removableLinkedSheetsFilter = $this->prophesize(AreRemovableLinkedSheetsCriteria::class);
        $handler = new LinkedSheetsListViewQueryHandler(
            $linkedSheetsRepository->reveal(),
            $removableLinkedSheetsFilter->reveal()
        );
        $linkedSheetsListViewQuery = new LinkedSheetsListViewQuery($event->reveal());

        // dependencies prophecies
        $removableLinkedSheetsFilter->meetCriteria(
            [$linkedSheets1->reveal(), $linkedSheets2->reveal()]
        )->shouldBeCalled()
            ->willReturn([$linkedSheets2->reveal()]);

        $linkedSheetsRepository->getByEvent($event->reveal())->willReturn(
            [$linkedSheets1->reveal(), $linkedSheets2->reveal()]
        );

        // run tests
        $result = $handler->handle($linkedSheetsListViewQuery);

        $linkedSheetsView1 = new LinkedSheetsView(1, ['Namco'], $linkedSheets1CreatedAt, false);
        $linkedSheetsView2 = new LinkedSheetsView(2, ['Bandai'], $linkedSheets2CreatedAt, true);
        $expected = new LinkedSheetsListView([$linkedSheetsView1, $linkedSheetsView2]);

        $this->assertEquals($expected, $result);
    }
}
