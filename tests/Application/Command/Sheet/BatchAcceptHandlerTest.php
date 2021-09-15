<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\BatchAccept;
use Proximum\Vimeet\Application\Command\Sheet\BatchAcceptHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\BatchAcceptJobQueue;

class BatchAcceptHandlerTest extends TestCase
{
    public function testHandle()
    {
        $admin = $this->prophesize(Admin::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->shouldBeCalled()->willReturn(1);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);

        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getId()->shouldBeCalled()->willReturn(3);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $batchJobQueue   = $this->prophesize(BatchAcceptJobQueue::class);

        $sheetRepository
            ->getSheetsUnacceptedById([1, 2, 3])
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()]);

        $sheetRepository->updateStateBySheetsId(
            [1, 2, 3],
            Sheet::STATE_ACCEPTED
        )->shouldBeCalled();

        $batchJobQueue->createJob([1, 2, 3], $admin->reveal())->shouldBeCalled();

        $command = new BatchAccept([1, 2, 3], $admin->reveal());

        $handler = new BatchAcceptHandler(
            $sheetRepository->reveal(),
            $batchJobQueue->reveal()
        );
        $result  = $handler->handle($command);

        $this->assertEquals(3, $result->count);
    }
}
