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
        $sheet2 = $this->prophesize(Sheet::class);
        $admin = $this->prophesize(Admin::class);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsById([1337, 2001])
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;
        $sheetRepository->updateStateBySheetsId([1337, 2001], 'refused')->shouldBeCalled();

        $batchRefuseJobQueue = $this->prophesize(BatchRefuseJobQueue::class);
        $batchRefuseJobQueue->createJob([1337, 2001], $admin->reveal());

        $batchRefuseHandler = new BatchRefuseHandler($sheetRepository->reveal(), $batchRefuseJobQueue->reveal());
        $result = $batchRefuseHandler->handle(new BatchRefuse([1337, 2001], $admin->reveal()));

        $this->assertEquals(new BatchResult(2, 'flash.admin.sheet_batch.refuse.success'), $result);
    }
}
