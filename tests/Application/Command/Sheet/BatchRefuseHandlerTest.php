<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchEnableDisable;
use Proximum\Vimeet\Application\Command\Sheet\BatchEnableDisableHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchRefuse;
use Proximum\Vimeet\Application\Command\Sheet\BatchRefuseHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchResult;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\BatchRefuseJobQueue;

class BatchRefuseHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->shouldBeCalled()->willReturn(1337);
        $sheet1->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->shouldBeCalled()->willReturn(2001);

        $admin = $this->prophesize(Admin::class);

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue
            ->sendEmailing($event->reveal(), [1337, 2001], 'sheet.refused')
            ->shouldBeCalled()
        ;

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
        $sheetRepository->refuseBySheetsId([1337, 2001])->shouldBeCalled();

        $batchRefuseJobQueue = $this->prophesize(BatchRefuseJobQueue::class);
        $batchRefuseJobQueue->createJob([1337, 2001], $admin->reveal());

        $batchRefuseHandler = new BatchRefuseHandler(
            $sheetRepository->reveal(),
            $batchEnableDisableHandler->reveal(),
            $batchRefuseJobQueue->reveal(),
            $jobQueue->reveal()
        );
        $result = $batchRefuseHandler->handle(new BatchRefuse([1337, 2001], $admin->reveal()));

        $this->assertEquals(
            new BatchResult([$sheet1->reveal(), $sheet2->reveal()], 'flash.admin.sheet_batch.refuse.success'),
            $result
        );
    }

    public function testIgnoredSheets()
    {
        $event = $this->prophesize(Event::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->willReturn(1337);
        $sheet1->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->willReturn(2001);

        $admin = $this->prophesize(Admin::class);

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue
            ->sendEmailing($event->reveal(), [1337], 'sheet.refused')
            ->shouldBeCalled()
        ;

        $batchEnableDisableHandler = $this->prophesize(BatchEnableDisableHandler::class);
        $batchEnableDisableHandler
            ->handle(new BatchEnableDisable([1337, 2001], false, $admin->reveal()))
            ->shouldBeCalled()
            ->willReturn(new BatchResult([$sheet1->reveal()], 'disabled message', 'Sheet 2011 is ignored'))
        ;

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsById([1337, 2001])
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;
        $sheetRepository->refuseBySheetsId([1337])->shouldBeCalled();

        $batchRefuseJobQueue = $this->prophesize(BatchRefuseJobQueue::class);
        $batchRefuseJobQueue->createJob([1337], $admin->reveal());

        $batchRefuseHandler = new BatchRefuseHandler(
            $sheetRepository->reveal(),
            $batchEnableDisableHandler->reveal(),
            $batchRefuseJobQueue->reveal(),
            $jobQueue->reveal()
        );
        $result = $batchRefuseHandler->handle(new BatchRefuse([1337, 2001], $admin->reveal()));

        $this->assertEquals(
            new BatchResult([$sheet1->reveal()], 'flash.admin.sheet_batch.refuse.warning', 'Sheet 2011 is ignored'),
            $result
        );
    }
}
