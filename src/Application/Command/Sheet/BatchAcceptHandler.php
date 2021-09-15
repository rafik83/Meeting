<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchAcceptHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var BatchJobQueueInterface */
    private $batchJobQueue;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param BatchJobQueueInterface   $batchJobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        BatchJobQueueInterface $batchJobQueue
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->batchJobQueue = $batchJobQueue;
    }

    /**
     * @param BatchAccept $batchAccept
     *
     * @return BatchResult
     */
    public function handle(BatchAccept $batchAccept)
    {
        $sheets = $this->sheetRepository->getSheetsUnacceptedById($batchAccept->ids);
        $sheetsId = array_map(function (Sheet $sheet) {
            return $sheet->getId();
        }, $sheets);

        if (!empty($sheetsId)) {
            $this->sheetRepository->updateStateBySheetsId(
                $batchAccept->ids,
                Sheet::STATE_ACCEPTED
            );

            $this->batchJobQueue->createJob($sheetsId, $batchAccept->admin);
        }

        return new BatchResult($sheets, $batchAccept->getMessage() . 'accept.success');
    }
}
