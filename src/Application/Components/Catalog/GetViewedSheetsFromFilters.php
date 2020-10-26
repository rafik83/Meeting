<?php

namespace Proximum\Vimeet\Application\Components\Catalog;

use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\CatalogConstant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;
use RuntimeException;

class GetViewedSheetsFromFilters
{
    /** @var SheetViewedRepositoryInterface */
    private $sheetViewedRepository;

    public function __construct(
        SheetViewedRepositoryInterface $sheetViewedRepository
    ) {
        $this->sheetViewedRepository = $sheetViewedRepository;
    }

    public function getFilteredByVisitSheetIds(array $filters, User $user, Sheet $sheet): ?array
    {
        // Take first selected value (select multiple should not be possible)
        $sheetVisitFilter = $filters[SearchFields::FILTER_BY_SHEET_VISIT][0] ?? null;
        if (empty($sheetVisitFilter)) {
            return null;
        }

        if ($sheetVisitFilter === CatalogConstant::FILTER_SHEET_SAW) {
            return array_column($this->sheetViewedRepository->getSheetsSeenByUserAndEvent($user, $sheet->getEvent()), 'id');
        }

        if ($sheetVisitFilter === CatalogConstant::FILTER_VIEWED_BY_SHEET) {
            return $this->sheetViewedRepository->getSheetIdsWhoViewedSheet($sheet);
        }

        throw new RuntimeException('Invalid sheet visit filter: '.$filters[SearchFields::FILTER_BY_SHEET_VISIT]);
    }
}
