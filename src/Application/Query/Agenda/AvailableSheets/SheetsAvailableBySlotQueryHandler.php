<?php

namespace Proximum\Vimeet\Application\Query\Agenda\AvailableSheets;

use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetsAvailableBySlotQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var VisibleParticipationTypes */
    private $visibleParticipationTypes;

    /**
     * @param SheetRepositoryInterface  $sheetRepository
     * @param VisibleParticipationTypes $visibleParticipationTypes
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        VisibleParticipationTypes $visibleParticipationTypes
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->visibleParticipationTypes = $visibleParticipationTypes;
    }

    /**
     * @deprecated This code is not used anymore.
     *
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
        }

        $allowedTypes = $this->visibleParticipationTypes->getAllowedTypesList($query->sheet);

        return $this->sheetRepository->countAvailableSheetsInCatalogWithTypesByEvent(
            $query->event,
            $allowedTypes,
            $query->slot,
            $excludedSheets
        );
    }
}
