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
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class BatchEnableDisableHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var BatchCatalogHandler
     */
    private $batchCatalogHandler;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

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
     * @param DelayedEventDispatcher     $eventDispatcher
     * @param BatchCatalogHandler        $batchCatalogHandler
     * @param \DateTimeInterface         $datetime
     * @param MeetingRepositoryInterface $meetingRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param BatchJobQueueInterface     $batchJobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        DelayedEventDispatcher $eventDispatcher,
        BatchCatalogHandler $batchCatalogHandler,
        \DateTimeInterface $datetime,
        MeetingRepositoryInterface $meetingRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        BatchJobQueueInterface $batchJobQueue
    ) {
        $this->sheetRepository     = $sheetRepository;
        $this->eventDispatcher     = $eventDispatcher;
        $this->batchCatalogHandler = $batchCatalogHandler;
        $this->datetime            = $datetime;
        $this->meetingRepository   = $meetingRepository;
        $this->sheetInfoGuesser    = $sheetInfoGuesser;
        $this->batchJobQueue       = $batchJobQueue;
    }

    /**
     * @param BatchEnableDisable $batchEnableDisable
     *
     * @return BatchResult
     */
    public function handle(BatchEnableDisable $batchEnableDisable)
    {
        $sheets               = $this->sheetRepository->getSheetsById($batchEnableDisable->ids);
        $message              = ($batchEnableDisable->state === true) ? 'enable.success' : 'disable.success';
        $ignoredSheets        = [];
        $ignoredSheetsMessage = '';

        foreach ($batchEnableDisable->ids as $index => $id) {
            if (isset($sheets[$id])) {
                $sheet = $sheets[$id];

                if ($batchEnableDisable->state === false
                    && $this->meetingRepository->countMeetingsOfSheet($sheet) > 0
                ) {
                    $ignoredSheets[] = $sheet;
                    $this->excludeSheetFromBatch($batchEnableDisable, $index);
                }
            }
        }

        if (!empty($batchEnableDisable->ids)) {
            $this->sheetRepository->updateEnableStateBySheetsId($batchEnableDisable->ids, $batchEnableDisable->state);

            $this->batchJobQueue->createJob(
                $batchEnableDisable->ids,
                $batchEnableDisable->admin,
                ['state' => $batchEnableDisable->state]
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

        return new BatchResult(count($sheets), $batchEnableDisable->getMessage() . $message, $ignoredSheetsMessage);
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
