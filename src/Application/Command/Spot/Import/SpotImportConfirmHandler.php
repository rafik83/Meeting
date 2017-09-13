<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot\Import;


use Proximum\Vimeet\Application\Components\Spot\SpotImporter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Domain\Spot\Import;
use Proximum\Vimeet\Domain\View\Spot\Import\SpotImportView;

class SpotImportConfirmHandler
{
    /** @var SpotImporter */
    private $spotImporter;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param SpotImporter $spotImporter
     * @param SpotRepositoryInterface $spotRepository
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(
        SpotImporter $spotImporter,
        SpotRepositoryInterface $spotRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->spotImporter = $spotImporter;
        $this->spotRepository = $spotRepository;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param SpotImportConfirm $command
     */
    public function handle(SpotImportConfirm $command)
    {
        $spotImportViews = $this->spotImporter->import(
            $command->event,
            $command->importedSpotFileName,
            $command->locale
        );

        $this->getExistentSpotAndDeleteAllByEvent($command->event);

        foreach ($spotImportViews as $spotImportView) {
            $this->removeInvalidSpotFromImport($spotImportView);
        }

        foreach ($spotImportViews as $spotImportView)
        {
            $spot = $this->createSpot($spotImportView->import, $command->event);

            foreach ($spotImportView->sheetViews as $sheetView) {
                $sheet = $this->sheetRepository->getSheetById($sheetView->id);
                $sheet->setSpot($spot);
            }

            $this->spotRepository->add($spot);
        }
    }

    /**
     * @param Import $importedSpot
     * @param Event $event
     *
     * @return Spot
     */
    private function createSpot(Import $importedSpot, Event $event): Spot
    {
        return new Spot(
            $importedSpot->reference,
            $event,
            $importedSpot->size,
            $importedSpot->meetingCapacity,
            $importedSpot->seatCapacity,
            $importedSpot->active,
            $importedSpot->priority,
            $importedSpot->visio
        );
    }

    /**
     * @param SpotImportView $spotImportView
     */
    private function removeInvalidSpotFromImport(SpotImportView $spotImportView)
    {
        if (!empty($spotImportView->errorMessages)) {
            unset($spotImportView);
        }
    }

    /**
     * @param Event $event
     */
    private function getExistentSpotAndDeleteAllByEvent(Event $event)
    {
        $existentSpots = $this->spotRepository->getAllByEvent($event);

        $this->spotRepository->removeBatchSpot($existentSpots, $event);
    }
}
