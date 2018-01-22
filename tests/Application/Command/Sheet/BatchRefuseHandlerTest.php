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
use Proximum\Vimeet\Application\Command\Sheet\BatchEnableDisable;
use Proximum\Vimeet\Application\Command\Sheet\BatchEnableDisableHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchRefuse;
use Proximum\Vimeet\Application\Command\Sheet\BatchRefuseHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchResult;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\BatchRefuseJobQueue;

class BatchRefuseHandlerTest extends TestCase
{
    public function testHandle()
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->willReturn(1337);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->willReturn(2001);

        $admin = $this->prophesize(Admin::class);

        $batchEnableDisableHandler = $this->prophesize(BatchEnableDisableHandler::class);
        $batchEnableDisableHandler
            ->handle(new BatchEnableDisable([1337, 2001], false, $admin->reveal()))
            ->shouldBeCalled()
            ->willReturn(new BatchResult([$sheet1->reveal(), $sheet2->reveal()], 'disabled success message'))
        ;

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsById([1337, 2001])
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;
        $sheetRepository->updateStateBySheetsId([1337, 2001], 'refused')->shouldBeCalled();

        $batchRefuseJobQueue = $this->prophesize(BatchRefuseJobQueue::class);
        $batchRefuseJobQueue->createJob([1337, 2001], $admin->reveal());

        $batchRefuseHandler = new BatchRefuseHandler(
            $sheetRepository->reveal(),
            $batchEnableDisableHandler->reveal(),
            $batchRefuseJobQueue->reveal()
        );
        $result = $batchRefuseHandler->handle(new BatchRefuse([1337, 2001], $admin->reveal()));

        $this->assertEquals(
            new BatchResult([$sheet1->reveal(), $sheet2->reveal()], 'flash.admin.sheet_batch.refuse.success'),
            $result
        );
    }

    public function testIgnoredSheets()
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->willReturn(1337);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->willReturn(2001);

        $admin = $this->prophesize(Admin::class);

        $batchDisable = new BatchEnableDisable([1337, 2001], false, $admin->reveal());
        $batchEnableDisableHandler = $this->prophesize(BatchEnableDisableHandler::class);
        $batchEnableDisableHandler
            ->handle($batchDisable)
            ->shouldBeCalled()
            ->willReturn(new BatchResult([$sheet1->reveal()], 'disabled message', 'Sheet 2011 is ignored'))
        ;

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsById([1337, 2001])
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;
        $sheetRepository->updateStateBySheetsId([1337], 'refused')->shouldBeCalled();

        $batchRefuseJobQueue = $this->prophesize(BatchRefuseJobQueue::class);
        $batchRefuseJobQueue->createJob([1337], $admin->reveal());

        $batchRefuseHandler = new BatchRefuseHandler(
            $sheetRepository->reveal(),
            $batchEnableDisableHandler->reveal(),
            $batchRefuseJobQueue->reveal()
        );
        $result = $batchRefuseHandler->handle(new BatchRefuse([1337, 2001], $admin->reveal()));

        $this->assertEquals(
            new BatchResult([$sheet1->reveal()], 'flash.admin.sheet_batch.refuse.warning', 'Sheet 2011 is ignored'),
            $result
        );
    }
}
