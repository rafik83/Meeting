<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchPrintInvoicesJobCreatorHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    public function __construct(SheetRepositoryInterface $sheetRepository, JobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
        $this->sheetRepository = $sheetRepository;
    }

    public function handle(BatchPrintInvoicesJobCreator $batchPrintInvoiceJobCreator): BatchResult
    {
        $sheets = $this->sheetRepository->findByIds($batchPrintInvoiceJobCreator->sheetIds);

        $this->jobQueue->printInvoicesPdf(
            $batchPrintInvoiceJobCreator->event,
            $batchPrintInvoiceJobCreator->sheetIds,
            $batchPrintInvoiceJobCreator->emailToNotify,
            $batchPrintInvoiceJobCreator->locale
        );

        return new BatchResult($sheets, $batchPrintInvoiceJobCreator->getMessage().'printInvoices.success');
    }
}
