<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchGenerateInvoice;
use Proximum\Vimeet\Application\Command\Sheet\BatchGenerateInvoiceHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchResult;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchGenerateInvoiceHandlerTest extends TestCase
{
    public function testHandle()
    {
        $sheet = $this->prophesize(Sheet::class);
        $event = $this->prophesize(Event::class);
        $date  = new \DateTime();
        $admin = new Admin('email@email.com', 'test', 'test', 'fr', 'test', 'test', 'ROLE_SUPER_ADMIN', $date);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->getSheetsById([1])->willReturn([$sheet->reveal()]);

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->generateInvoice($event->reveal(), [1], $admin)->shouldBeCalled();

        $command = new BatchGenerateInvoice($event->reveal(), [1], $admin);
        $handler = new BatchGenerateInvoiceHandler($sheetRepository->reveal(), $jobQueue->reveal());
        $result  = $handler->handle($command);

        $this->assertEquals(
            new BatchResult([$sheet->reveal()], 'flash.admin.sheet_batch.generateInvoiceBatch.success'),
            $result
        );
    }
}
