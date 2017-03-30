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

class BatchCatalogHandler
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
     * BatchCatalogHandler constructor.
     *
     * @param SheetRepositoryInterface   $sheetRepository
     * @param DelayedEventDispatcher     $eventDispatcher
     * @param \DateTimeInterface         $datetime
     * @param MeetingRepositoryInterface $meetingRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param BatchJobQueueInterface     $batchJobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $datetime,
        MeetingRepositoryInterface $meetingRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        BatchJobQueueInterface $batchJobQueue
    ) {
        $this->sheetRepository   = $sheetRepository;
        $this->eventDispatcher   = $eventDispatcher;
        $this->datetime          = $datetime;
        $this->meetingRepository = $meetingRepository;
        $this->sheetInfoGuesser  = $sheetInfoGuesser;
        $this->batchJobQueue     = $batchJobQueue;
    }

    /**
     * @param BatchCatalog $command
     *
     * @return BatchResult
     */
    public function handle(BatchCatalog $command)
    {
        $sheets               = $this->sheetRepository->getSheetsById($command->ids);
        $ignoredSheets        = [];
        $ignoredSheetsMessage = '';
        $message              = ($command->state) ? 'catalog.add.success' : 'catalog.remove.success';

        foreach ($sheets as $sheet) {
            // If try to remove from catalog
            if ($command->state === false) {
                if ($this->meetingRepository->countMeetingsOfSheet($sheet) > 0) {
                    $ignoredSheets[]   = $sheet;
                    $this->excludeSheetFromBatch($command, $sheet);
                }
            } elseif ($command->state === true) {
                if (!$sheet->isEnabled()) {
                    $ignoredSheets[] = $sheet;
                    $this->excludeSheetFromBatch($command, $sheet);
                }
            }
        }

        // update sheets in catalog state and set in catalog date
        $this->sheetRepository->updateInCatalogBySheetsId($command->ids, $command->state);

        if (count($ignoredSheets) > 0) {
            $message = $command->state ? 'catalog.add.warning' : 'catalog.remove.warning';
            $locale = $ignoredSheets[0]->getEvent()->getAvailableLocale($command->admin->getLocale());

            // Format sheets title to display them in flash warning message
            $ignoredSheetsMessage = implode(', ', array_map(function (Sheet $sheet) use ($locale) {
                return $this->sheetInfoGuesser->guessSheetTitle(
                    $sheet,
                    $locale
                );
            }, $ignoredSheets));
        }

        $this->batchJobQueue->createJob($command->ids, $command->admin, [
            'state' => $command->state
        ]);

        return new BatchResult(count($sheets), $command->getMessage() . $message, $ignoredSheetsMessage);
    }

    /**
     * @param BatchCatalog $command
     * @param Sheet        $sheet
     */
    private function excludeSheetFromBatch(BatchCatalog $command, Sheet $sheet)
    {
        $ignoredSheetIndex = array_search($sheet->getId(), $command->ids);
        if ($ignoredSheetIndex !== false) {
            $command->ids = array_slice($command->ids, $ignoredSheetIndex);
        }
    }
}
