<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchDraftHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var BatchJobQueueInterface
     */
    private $batchJobQueue;

    /**
     * @var JobQueueInterface
     */
    private $jobQueue;

    /**
     * BatchPendingHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param BatchJobQueueInterface   $batchJobQueue
     * @param JobQueueInterface        $jobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        BatchJobQueueInterface $batchJobQueue,
        JobQueueInterface $jobQueue
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->batchJobQueue   = $batchJobQueue;
        $this->jobQueue        = $jobQueue;
    }

    /**
     * @param BatchDraft $batchPending
     *
     * @return BatchResult
     */
    public function handle(BatchDraft $batchPending)
    {
        $sheets = $this->sheetRepository->getSheetsById($batchPending->ids);

        $this->sheetRepository->updateValidationState(
            $batchPending->ids,
            Sheet::STATE_VALIDATION_DRAFT
        );

        if (!empty($batchPending->ids)) {
            // send email
            $this->jobQueue->sendEmailing($batchPending->event, $batchPending->ids, Events::SHEET_VALIDATION_DRAFT);

            $this->batchJobQueue->createJob(
                $batchPending->ids,
                $batchPending->admin
            );
        }

        return new BatchResult($sheets, $batchPending->getMessage() . 'draft.success');
    }
}
