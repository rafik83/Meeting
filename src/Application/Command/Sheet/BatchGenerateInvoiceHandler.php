<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchGenerateInvoiceHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param JobQueueInterface        $jobQueue
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, JobQueueInterface $jobQueue)
    {
        $this->sheetRepository = $sheetRepository;
        $this->jobQueue = $jobQueue;
    }

    /**
     * @param BatchGenerateInvoice $batchGenerateInvoice
     *
     * @return BatchResult
     */
    public function handle(BatchGenerateInvoice $batchGenerateInvoice)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchGenerateInvoice->ids);

        $this->jobQueue->generateInvoice(
            $batchGenerateInvoice->event,
            $batchGenerateInvoice->ids,
            $batchGenerateInvoice->admin
        );

        return new BatchResult(
            $sheets,
            $batchGenerateInvoice->getMessage() . 'generateInvoiceBatch.success'
        );
    }
}
