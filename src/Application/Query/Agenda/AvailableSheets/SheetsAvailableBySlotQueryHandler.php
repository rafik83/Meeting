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
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetsAvailableBySlotQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var VisibleParticipationTypes */
    private $visibleParticipationTypes;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param ParticipantRepositoryInterface $participantRepository
     * @param RequestRepositoryInterface     $requestRepository
     * @param VisibleParticipationTypes      $visibleParticipationTypes
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        RequestRepositoryInterface $requestRepository,
        VisibleParticipationTypes $visibleParticipationTypes
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->participantRepository = $participantRepository;
        $this->requestRepository = $requestRepository;
        $this->visibleParticipationTypes = $visibleParticipationTypes;
    }

    /**
     * @param SheetsAvailableBySlotQuery $query
     *
     * @return int
     */
    public function handle(SheetsAvailableBySlotQuery $query): int
    {
        $requestForExcludedSheet = $this
            ->requestRepository
            ->getApprovedAndRefusedRequestBySheet($query->excludedSheet);

        $excludedSheets = [];

        /** @var Request $request */
        foreach ($requestForExcludedSheet as $request) {
            $sheetMet = $request->getSheetMet($query->excludedSheet);
            $excludedSheets[$sheetMet->getId()] = $sheetMet;
        }

        $sheets = $this->sheetRepository->getSheetsInCatalogByEvent($query->event, [], $excludedSheets);
        $availableSheets = [];
        $participants    = [];

        foreach ($sheets as $sheet) {
            $allowedTypes = $this->visibleParticipationTypes->getAllowedTypesList($sheet);

            if (in_array($sheet->getType(), $allowedTypes)) {
                $availableSheets[] = $sheet;

                foreach ($sheet->getParticipants() as $participant) {
                    $participants[] = $participant;
                }
            }
        }

        $availableParticipants = $this->participantRepository->getAvailableParticipants(
            $participants,
            $query->slot->getBegin(),
            $query->slot->getEnd()
        );

        $filteredAvailableSheets = [];

        foreach ($availableParticipants as $availableParticipant) {
            foreach ($availableSheets as $availableSheet) {
                if ($availableParticipant->getSheet() === $availableSheet
                    && !in_array($availableSheet, $filteredAvailableSheets)
                ) {
                    $filteredAvailableSheets[] = $availableSheet;
                }
            }
         }

       return count($filteredAvailableSheets);
    }
}
