<?php

namespace Proximum\Vimeet\Application\Components\Catalog;

use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\CatalogConstant;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;
use RuntimeException;

class GetViewedSheetsFromFilters
{
    /** @var SheetViewedRepositoryInterface */
    private $sheetViewedRepository;

    /** @var int[] */
    private $sheetIdsSaw;

    /** @var int[] */
    private $sheetIdsViewedBySheet;

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
            return $this->getSheetIdsSaw($user, $sheet->getEvent());
        }

        if ($sheetVisitFilter === CatalogConstant::FILTER_VIEWED_BY_SHEET) {
            return $this->getSheetIdsViewedBySheet($sheet);
        }

        throw new RuntimeException('Invalid sheet visit filter: '.$filters[SearchFields::FILTER_BY_SHEET_VISIT]);
    }

    public function getSheetIdsSaw(User $user, Event $event): array
    {
        if ($this->sheetIdsSaw !== null) {
            return $this->sheetIdsSaw;
        }

        $this->sheetIdsSaw = array_column($this->sheetViewedRepository->getSheetsSeenByUserAndEvent($user, $event), 'id');

        return $this->sheetIdsSaw;
    }

    public function getSheetIdsViewedBySheet(Sheet $sheet): array
    {
        if ($this->sheetIdsViewedBySheet !== null) {
            return $this->sheetIdsViewedBySheet;
        }

        $this->sheetIdsViewedBySheet = $this->sheetViewedRepository->getSheetIdsWhoViewedSheet($sheet);

        return $this->sheetIdsViewedBySheet;
    }
}
