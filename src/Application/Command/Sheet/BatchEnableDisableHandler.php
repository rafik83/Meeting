<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\HappeningParticipation\EnableDisableManager;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetEnableDisableEvent;
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
     * @var EnableDisableManager
     */
    private $enableDisableManager;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * BatchEnableDisableHandler constructor.
     *
     * @param SheetRepositoryInterface   $sheetRepository
     * @param DelayedEventDispatcher     $eventDispatcher
     * @param BatchCatalogHandler        $batchCatalogHandler
     * @param \DateTimeInterface         $datetime
     * @param EnableDisableManager       $enableDisableManager
     * @param MeetingRepositoryInterface $meetingRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        DelayedEventDispatcher $eventDispatcher,
        BatchCatalogHandler $batchCatalogHandler,
        \DateTimeInterface $datetime,
        EnableDisableManager $enableDisableManager,
        MeetingRepositoryInterface $meetingRepository,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->sheetRepository      = $sheetRepository;
        $this->eventDispatcher      = $eventDispatcher;
        $this->batchCatalogHandler  = $batchCatalogHandler;
        $this->datetime             = $datetime;
        $this->enableDisableManager = $enableDisableManager;
        $this->meetingRepository    = $meetingRepository;
        $this->sheetInfoGuesser     = $sheetInfoGuesser;
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

        foreach ($sheets as $sheet) {

            if ($batchEnableDisable->state === false && $this->meetingRepository->countMeetingsOfSheet($sheet) > 0) {
                $ignoredSheets[] = $sheet;

                continue;
            }

            $this->enableDisableManager->update($sheet, $batchEnableDisable->state);
            $this->sheetRepository->set($sheet->setEnable($batchEnableDisable->state));

            // remove sheet from catalog if sheet is disable
            if ($batchEnableDisable->state === false) {
                $this->batchCatalogHandler->handle(new BatchCatalog(
                    $batchEnableDisable->ids,
                    $batchEnableDisable->state,
                    $batchEnableDisable->admin
                ));
            }

            $this->eventDispatcher->dispatch(
                Events::SHEET_ENABLE_DISABLE,
                new SheetEnableDisableEvent(
                    $sheet,
                    $batchEnableDisable->admin,
                    $this->datetime,
                    $batchEnableDisable->state
                )
            );
        }

        if (count($ignoredSheets) > 0) {
            $message = 'disable.warning';
            $locale = $ignoredSheets[0]->getEvent()->getAvailableLocale($batchEnableDisable->admin->getLocale());
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
}
