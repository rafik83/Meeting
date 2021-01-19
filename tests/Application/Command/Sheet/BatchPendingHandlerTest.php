<?php

namespace Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchPending;
use Proximum\Vimeet\Application\Command\Sheet\BatchPendingHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\AdminFactory;

class BatchPendingHandlerTest extends TestCase
{
    public function testHandle()
    {
        $admin  = AdminFactory::create();
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $batchJobQueue   = $this->prophesize(BatchJobQueueInterface::class);

        $sheetRepository->getSheetsNotPendingById([1, 2, 3])
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()]);

        $sheetRepository->updateStateBySheetsId(
            [1, 2, 3],
            Sheet::STATE_PENDING
        )->shouldBeCalled(3);

        $batchJobQueue->createJob([1, 2, 3], $admin)->shouldBeCalled();

        $handler = new BatchPendingHandler(
            $sheetRepository->reveal(),
            $batchJobQueue->reveal()
        );

        $result = $handler->handle(new BatchPending([1, 2, 3], $admin));

        $this->assertEquals(3, $result->count);
    }
}
