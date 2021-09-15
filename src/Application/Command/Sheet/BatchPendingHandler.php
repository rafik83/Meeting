<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchPendingHandler
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
     * BatchValidateHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param BatchJobQueueInterface   $batchJobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        BatchJobQueueInterface $batchJobQueue
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->batchJobQueue   = $batchJobQueue;
    }

    /**
     * @param BatchPending $batchPending
     *
     * @return BatchResult
     */
    public function handle(BatchPending $batchPending): BatchResult
    {
        // Get sheets not pending by id
        $sheets = $this->sheetRepository->getSheetsNotPendingById($batchPending->ids);

        if (!empty($batchPending->ids)) {
            $this->sheetRepository->updateStateBySheetsId(
                $batchPending->ids,
                Sheet::STATE_PENDING
            );

            $this->batchJobQueue->createJob($batchPending->ids, $batchPending->admin);
        }

        return new BatchResult($sheets, $batchPending->getMessage() . 'pending.success');
    }
}
