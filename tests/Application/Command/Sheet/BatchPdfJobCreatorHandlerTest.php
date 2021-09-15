<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchPdfJobCreator;
use Proximum\Vimeet\Application\Command\Sheet\BatchPdfJobCreatorHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchResult;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchPdfJobCreatorHandlerTest extends TestCase
{
    public function testHandle()
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);
        $admin->getEmail()->willReturn('admin@example.net');

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->findByIds([11, 13])->willReturn([$sheet1->reveal(), $sheet2->reveal()]);

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue
            ->printSheetsPdf($event->reveal(), [11, 13], 'admin@example.net', 'fr', 'alphabetical')
            ->shouldBeCalled()
        ;

        $command = new BatchPdfJobCreator($event->reveal(), [11, 13], $admin->reveal(), 'fr', 'alphabetical');
        $handler = new BatchPdfJobCreatorHandler($sheetRepository->reveal(), $jobQueue->reveal());
        $result = $handler->handle($command);

        $expected = new BatchResult([$sheet1->reveal(), $sheet2->reveal()], 'flash.admin.sheet_batch.printPdf.success');

        $this->assertEquals($expected, $result);
    }
}
