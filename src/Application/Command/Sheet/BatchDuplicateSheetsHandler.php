<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Domain\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Event\ExtraData\Type as ExtraDataType;
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
            $type = $batchDuplicateSheets->type;
            $ids = implode(', ', $batchDuplicateSheets->ids);

            $extraData = $this->extraDataRepository
                ->getExtraDataForEvent($type->getEvent(), ExtraDataType::DUPLICATE_SHEET_IDS);

            if (!$extraData instanceof ExtraData) {
                $this->extraDataRepository->add(
                    new ExtraData(
                        $type->getEvent(),
                        Type::DUPLICATE_SHEET_IDS,
                        $ids,
                        $this->datetime
                    )
                );
            } else {
                $extraData->update($ids, $this->datetime);
                $this->extraDataRepository->set($extraData);
            }

            $this->jobQueue->createJob(
                $batchDuplicateSheets->ids,
                $batchDuplicateSheets->admin,
                [
                    'typeId' => $type->getId(),
                ]
            );
        }

        return new BatchResult(
            $sheets,
            'flash.admin.sheet_batch.duplicate_sheet.success'
        );
    }
}
