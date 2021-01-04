<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchValidationValidateHandler
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
     * BatchValidationValidateHandler constructor.
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
     * @param BatchValidationValidate $batch
     *
     * @return BatchResult
     */
    public function handle(BatchValidationValidate $batch)
    {
        $sheets = $this->sheetRepository->getSheetsById($batch->ids);

        $this->sheetRepository->updateValidationState(
            $batch->ids,
            Sheet::STATE_VALIDATION_VALIDATED
        );

        if (!empty($batch->ids)) {
            // reindex sheets
            $this->jobQueue->indexSheets($batch->ids);

            // send email
            $this->jobQueue->sendEmailing($batch->event, $batch->ids, Events::SHEET_VALIDATION_VALIDATE);

            $this->batchJobQueue->createJob($batch->ids, $batch->admin);
        }

        return new BatchResult($sheets, $batch->getMessage() . 'validation.validate.success');
    }
}
