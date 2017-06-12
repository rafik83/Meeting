<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\BatchAssignToGroup;
use Proximum\Vimeet\Application\Command\Sheet\BatchAssignToGroupHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchResult;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchAssignToGroupHandlerTest extends TestCase
{
    public function testAssignHandle()
    {
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);

        $group = $this->prophesize(Sheet\Group::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheet1->getId()->willReturn(1);
        $sheet1->hasGroup()->willReturn(false);

        $sheet2->getId()->willReturn(2);
        $sheet2->hasGroup()->willReturn(false);

        $sheet1->setGroup($group)->shouldBeCalled();
        $sheet2->setGroup($group)->shouldBeCalled();

        $sheetRepository->getSheetsById([1, 2])->shouldBeCalled()->willReturn(
            [
                $sheet1->reveal(),
                $sheet2->reveal(),
            ]
        );

        $sheetRepository->set($sheet1->reveal())->shouldBeCalled();
        $sheetRepository->set($sheet2->reveal())->shouldBeCalled();

        $sheetInfoGuesser->guessSheetTitle($sheet1->reveal(), 'fr')->shouldNotBeCalled();
        $sheetInfoGuesser->guessSheetTitle($sheet2->reveal(), 'fr')->shouldNotBeCalled();

        $handler = new BatchAssignToGroupHandler(
            $sheetRepository->reveal(),
            $sheetInfoGuesser->reveal()
        );

        $result = $handler->handle(new BatchAssignToGroup([1, 2], $group->reveal(), 'fr'));

        $expected = new BatchResult(2, 'flash.admin.sheet_batch.assignToGroup.success');

        $this->assertEquals($expected, $result);
    }

    public function testAssignWithIgnoredSheetsHandle()
    {
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);

        $group = $this->prophesize(Sheet\Group::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);

        $sheet1->getId()->willReturn(1);
        $sheet1->hasGroup()->willReturn(false);

        $sheet2->getId()->willReturn(2);
        $sheet2->hasGroup()->willReturn(false);

        $sheet3->getId()->willReturn(3);
        $sheet3->hasGroup()->willReturn(true);

        $sheet1->setGroup($group)->shouldBeCalled();
        $sheet2->setGroup($group)->shouldBeCalled();

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn(
            [
                $sheet1->reveal(),
                $sheet2->reveal(),
                $sheet3->reveal(),
            ]
        );

        $sheetRepository->set($sheet1->reveal())->shouldBeCalled();
        $sheetRepository->set($sheet2->reveal())->shouldBeCalled();

        $sheetInfoGuesser->guessSheetTitle($sheet3->reveal(), 'fr')->shouldBeCalled()->willReturn(
            'Flash message with ignored sheets'
        );

        $handler = new BatchAssignToGroupHandler(
            $sheetRepository->reveal(),
            $sheetInfoGuesser->reveal()
        );

        $result = $handler->handle(new BatchAssignToGroup([1, 2, 3], $group->reveal(), 'fr'));

        $expected = new BatchResult(
            2,
            'flash.admin.sheet_batch.assignToGroup.ignoredSheets',
            'Flash message with ignored sheets'
        );

        $this->assertEquals($expected, $result);
    }
}
