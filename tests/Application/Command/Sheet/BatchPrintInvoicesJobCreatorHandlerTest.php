<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchPrintInvoicesJobCreator;
use Proximum\Vimeet\Application\Command\Sheet\BatchPrintInvoicesJobCreatorHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchResult;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchPrintInvoicesJobCreatorHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);
        $sheetIds = [1, 2];
        $admin = $this->prophesize(Admin::class);
        $sheets = [$this->prophesize(Sheet::class)->reveal(), $this->prophesize(Sheet::class)->reveal()];

        $admin->getEmail()->willReturn('tete@toto.fr');

        // prophecy dependencies
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $sheetRepository->findByIds($sheetIds)->shouldBeCalled()
            ->willReturn($sheets)
        ;

        $jobQueue->printInvoicesPdf($event->reveal(), $sheetIds, 'tete@toto.fr', 'fr')
            ->shouldBeCalled()
        ;

        // run tests
        $query = new BatchPrintInvoicesJobCreator($event->reveal(), $sheetIds, $admin->reveal(), 'fr');
        $handle = new BatchPrintInvoicesJobCreatorHandler($sheetRepository->reveal(), $jobQueue->reveal());
        $result = $handle->handle($query);

        $this->assertEquals(new BatchResult($sheets, $query->getMessage().'printInvoices.success'), $result);
    }
}
