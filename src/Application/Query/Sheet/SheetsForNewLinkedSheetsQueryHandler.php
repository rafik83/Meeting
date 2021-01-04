<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\View\SheetView;

class SheetsForNewLinkedSheetsQueryHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param SheetsForNewLinkedSheetsQuery $query
     *
     * @return SheetView[]
     */
    public function handle(SheetsForNewLinkedSheetsQuery $query)
    {
        $sheets = $this->sheetRepository->getNotLinkedSheets($query->event);

        $sheetsForNewLinkedSheetsList = [];
        foreach ($sheets as $sheet) {
            $sheetsForNewLinkedSheetsList[] = new SheetView(
                $sheet->getId(),
                $sheet->getTitle()
            );
        }

        return $sheetsForNewLinkedSheetsList;
    }
}
