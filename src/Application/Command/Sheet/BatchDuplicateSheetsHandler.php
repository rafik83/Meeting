<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Domain\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchDuplicateSheetsHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var BatchJobQueueInterface */
    private $jobQueue;

    /** @var \DateTimeInterface */
    private $datetime;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        BatchJobQueueInterface $jobQueue,
        \DateTimeInterface $datetime
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->jobQueue = $jobQueue;
        $this->datetime = $datetime;
    }

    public function handle(BatchDuplicateSheets $batchDuplicateSheets): BatchResult
    {
        $sheets = $this->sheetRepository->getSheetsById($batchDuplicateSheets->ids);

        if (!empty($sheets)) {
            $extraData = new ExtraData(
                $batchDuplicateSheets->type->getEvent(),
                Type::ADMIN_SHEET_BATCH_IDS,
                implode(', ', $batchDuplicateSheets->ids),
                $this->datetime
            );

            $this->extraDataRepository->add($extraData);

            $this->jobQueue->createJob(
                $batchDuplicateSheets->ids,
                $batchDuplicateSheets->admin,
                [
                    'typeId' => $batchDuplicateSheets->type->getId(),
                    'extraDataId' => $extraData->getId(),
                    'originalEventId' => $batchDuplicateSheets->originalEvent->getId(),
                ]
            );
        }

        return new BatchResult(
            $sheets,
            'flash.admin.sheet_batch.duplicate_sheet.success'
        );
    }
}
