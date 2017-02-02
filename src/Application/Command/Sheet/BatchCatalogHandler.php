<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Request\EnableDisableManager;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetCatalogEvent;
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
     * @var EnableDisableManager
     */
    private $enableDisableManager;

    /**
     * BatchCatalogHandler constructor.
     *
     * @param SheetRepositoryInterface   $sheetRepository
     * @param DelayedEventDispatcher     $eventDispatcher
     * @param \DateTimeInterface         $datetime
     * @param MeetingRepositoryInterface $meetingRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param EnableDisableManager       $enableDisableManager
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $datetime,
        MeetingRepositoryInterface $meetingRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        EnableDisableManager $enableDisableManager
    ) {
        $this->sheetRepository   = $sheetRepository;
        $this->eventDispatcher   = $eventDispatcher;
        $this->datetime          = $datetime;
        $this->meetingRepository = $meetingRepository;
        $this->sheetInfoGuesser  = $sheetInfoGuesser;
        $this->enableDisableManager = $enableDisableManager;
    }

    /**
     * @param BatchCatalog $command
     *
     * @return BatchResult
     */
    public function handle(BatchCatalog $command)
    {
        $sheets = $this->sheetRepository->getSheetsById($command->ids);
        $ignoredSheets = [];
        $ignoredSheetsMessage = '';
        $message = ($command->state) ? 'catalog.add.success' : 'catalog.remove.success';

        foreach ($sheets as $sheet) {
            // trace state in catalog change only
            if ($sheet->isInCatalog() !== $command->state) {
                $this->eventDispatcher->dispatch(
                    Events::SHEET_CATALOG,
                    new SheetCatalogEvent(
                        $sheet,
                        $command->admin,
                        $this->datetime,
                        $command->state
                    )
                );
            }

            if ($this->meetingRepository->countMeetingsOfSheet($sheet) > 0) {
                $ignoredSheets[] = $sheet;
            } else {
                $sheet->setInCatalog($command->state);

                if ($command->state === true) {
                    $sheet->setInCatalogAt($this->datetime);
                }
                $this->enableDisableManager->update($sheet, $command->state);
                $this->sheetRepository->set($sheet);
            }
        }

        if (count($ignoredSheets) > 0) {
            $message = 'catalog.remove.warning';
            $locale = $ignoredSheets[0]->getEvent()->getAvailableLocale($command->admin->getLocale());
            // Format sheets title to display them in flash warning message
            $ignoredSheetsMessage = implode(', ', array_map(function (Sheet $sheet) use ($locale) {
                return $this->sheetInfoGuesser->guessSheetTitle(
                    $sheet,
                    $locale
                );
            }, $ignoredSheets));
        }

        return new BatchResult(count($sheets), $command->getMessage() . $message, $ignoredSheetsMessage);
    }
}
