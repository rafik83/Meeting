<?php

namespace Proximum\Vimeet\Application\Query\Sheet\LinkedSheets\Admin;

use Proximum\Vimeet\Application\Criteria\LinkedSheets\AreRemovableLinkedSheetsCriteria;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;

class LinkedSheetsListViewQueryHandler
{
    /**
     * @var LinkedSheetsRepositoryInterface
     */
    private $linkedSheetsRepository;

    /** @var AreRemovableLinkedSheetsCriteria */
    private $removableLinkedSheetsFilter;

    public function __construct(
        LinkedSheetsRepositoryInterface $linkedSheetsRepository,
        AreRemovableLinkedSheetsCriteria $removableLinkedSheetsFilter
    ) {
        $this->linkedSheetsRepository = $linkedSheetsRepository;
        $this->removableLinkedSheetsFilter = $removableLinkedSheetsFilter;
    }

    public function handle(LinkedSheetsListViewQuery $query): LinkedSheetsListView
    {
        $linkedSheetsViews = [];
        $someLinkedSheets = $this->linkedSheetsRepository->getByEvent($query->event);

        $removableLinkedSheets = $this->removableLinkedSheetsFilter->meetCriteria($someLinkedSheets);

        foreach ($someLinkedSheets as $linkedSheets) {
            $titles = [];
            foreach ($linkedSheets->getSheets() as $sheet) {
                $titles[] = $sheet->getTitle();
            }
            $isRemovable = in_array($linkedSheets, $removableLinkedSheets, true);
            $linkedSheetsViews[] = new LinkedSheetsView($linkedSheets->getId(), $titles, $linkedSheets->getCreatedAt(), $isRemovable);
        }

        return new LinkedSheetsListView($linkedSheetsViews);
    }
}
