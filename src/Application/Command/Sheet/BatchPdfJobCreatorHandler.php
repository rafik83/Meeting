<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchPdfJobCreatorHandler
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
        $this->jobQueue = $jobQueue;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param BatchPdfJobCreator $batchPdfJobCreator
     *
     * @return BatchResult
     */
    public function handle(BatchPdfJobCreator $batchPdfJobCreator): BatchResult
    {
        $sheets = $this->sheetRepository->findByIds($batchPdfJobCreator->sheetIds);

        $this->jobQueue->printSheetsPdf(
            $batchPdfJobCreator->event,
            $batchPdfJobCreator->sheetIds,
            $batchPdfJobCreator->emailToNotify,
            $batchPdfJobCreator->locale,
            $batchPdfJobCreator->orderBy
        );

        return new BatchResult(
            $sheets,
            $batchPdfJobCreator->getMessage() . 'printPdf.success'
        );
    }
}
