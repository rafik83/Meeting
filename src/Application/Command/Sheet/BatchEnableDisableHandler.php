<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchEnableDisableHandler
{
    const STATE_ENABLE  = 'enable';
    const STATE_DISABLE = 'disable';

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var BatchJobQueueInterface
     */
    private $batchJobQueue;

    /**
     * BatchEnableDisableHandler constructor.
     *
     * @param SheetRepositoryInterface   $sheetRepository
     * @param MeetingRepositoryInterface $meetingRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param BatchJobQueueInterface     $batchJobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        MeetingRepositoryInterface $meetingRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        BatchJobQueueInterface $batchJobQueue
    ) {
        $this->sheetRepository   = $sheetRepository;
        $this->meetingRepository = $meetingRepository;
        $this->sheetInfoGuesser  = $sheetInfoGuesser;
        $this->batchJobQueue     = $batchJobQueue;
    }

    /**
     * @param BatchEnableDisable $batchEnableDisable
     *
     * @return BatchResult
     */
    public function handle(BatchEnableDisable $batchEnableDisable): BatchResult
    {
        $sheets = $this->sheetRepository->getSheetsById($batchEnableDisable->ids);
        $message = ($batchEnableDisable->state === true) ? 'enable.success' : 'disable.success';
        $processedSheets = [];
        $ignoredSheets = [];
        $ignoredSheetsMessage = '';

        $meetings = $this->meetingRepository->countMeetingsOfSheetByIds($batchEnableDisable->ids);

        foreach ($batchEnableDisable->ids as $index => $id) {
            if (isset($sheets[$id])) {
                $sheet = $sheets[$id];

                if ($batchEnableDisable->state === false
                    && isset($meetings[$id])
                    && $meetings[$id] > 0
                ) {
                    $ignoredSheets[] = $sheet;
                    $this->excludeSheetFromBatch($batchEnableDisable, $index);
                } else {
                    $processedSheets[] = $sheet;
                }
            }
        }

        if (!empty($batchEnableDisable->ids)) {
            $this->sheetRepository->updateEnableStateBySheetsId($batchEnableDisable->ids, $batchEnableDisable->state);

            $this->batchJobQueue->createJob(
                $batchEnableDisable->ids,
                $batchEnableDisable->admin,
                ['state' => $batchEnableDisable->state ? self::STATE_ENABLE : self::STATE_DISABLE]
            );
        }

        if (count($ignoredSheets) > 0) {
            $message = 'disable.warning';
            $locale  = $ignoredSheets[0]->getEvent()->getAvailableLocale($batchEnableDisable->admin->getLocale());
            // Format sheets title to display them in flash warning message
            $ignoredSheetsMessage = implode(', ',
                array_map(function (Sheet $sheet) use ($locale) {
                    return $this->sheetInfoGuesser->guessSheetTitle(
                        $sheet,
                        $locale
                    );
                }, $ignoredSheets));
        }

        return new BatchResult($processedSheets, $batchEnableDisable->getMessage() . $message, $ignoredSheetsMessage);
    }

    /**
     * Remove a specific sheet id from the pull of batch IDs
     *
     * @param BatchEnableDisable $command
     * @param int                $index
     */
    private function excludeSheetFromBatch(BatchEnableDisable $command, $index)
    {
        unset($command->ids[$index]);
    }
}
