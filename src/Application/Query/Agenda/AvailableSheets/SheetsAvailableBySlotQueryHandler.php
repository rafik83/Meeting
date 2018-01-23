<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\AvailableSheets;

use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetsAvailableBySlotQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var VisibleParticipationTypes */
    private $visibleParticipationTypes;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param ParticipantRepositoryInterface $participantRepository
     * @param VisibleParticipationTypes      $visibleParticipationTypes
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        VisibleParticipationTypes $visibleParticipationTypes
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->participantRepository = $participantRepository;
        $this->visibleParticipationTypes = $visibleParticipationTypes;
    }

    /**
     * @param SheetsAvailableBySlotQuery $query
     *
     * @return int
     */
    public function handle(SheetsAvailableBySlotQuery $query): int
    {
        $excludedSheets = [$query->sheet->getId() => $query->sheet];
        $sheetsMet = $this->sheetRepository->getSheetsWithRequestWithSheet($query->sheet);

        foreach ($sheetsMet as $sheetMet) {
            $excludedSheets[$sheetMet->getId()] = $sheetMet;
        };

        $allowedTypes = $this->visibleParticipationTypes->getAllowedTypesList($query->sheet);
        $sheets       = $this->sheetRepository->getSheetsInCatalogWithTypesByEvent(
            $query->event,
            $allowedTypes,
            $excludedSheets
        );
        $participants = [];

        foreach ($sheets as $sheet) {
            foreach ($sheet->getParticipants()->toArray() as $participant) {
                $participants[] = $participant;
            }
        }

        $availableParticipants = $this->participantRepository->getAvailableParticipants(
            $participants,
            $query->slot->getBegin(),
            $query->slot->getEnd()
        );

        $filteredAvailableSheets = [];

        foreach ($availableParticipants as $availableParticipant) {
            $filteredAvailableSheets[$availableParticipant->getSheet()->getId()] = $availableParticipant->getSheet();
        }

       return count($filteredAvailableSheets);
    }
}
