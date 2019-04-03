<?php

namespace Proximum\Vimeet\Domain\Sheet\LinkedSheets;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class RemovableLinkedSheetsFilter
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    public function isSatisfiedBy(array $someLinkedSheets): array
    {
        $everySheets = [];
        foreach ($someLinkedSheets as $linkedSheets) {
            foreach ($linkedSheets->getSheets() as $sheet) {
                $everySheets[] = $sheet;
            }
        }

        $sheetsWithMeetings = $this->sheetRepository->filterWithMeetings($everySheets);

        $removableLinkedSheets = [];
        foreach ($someLinkedSheets as $linkedSheets) {
            if (!$this->hasLinkedSheetsMeetings($linkedSheets, $sheetsWithMeetings)) {
                $removableLinkedSheets[] = $linkedSheets;
            }
        }

        return $removableLinkedSheets;
    }

    /**
     * @param LinkedSheets $linkedSheets
     * @param Sheet[]      $sheetsWithMeetings
     *
     * @return bool
     */
    private function hasLinkedSheetsMeetings(LinkedSheets $linkedSheets, array $sheetsWithMeetings): bool
    {
        foreach ($linkedSheets->getSheets() as $sheet) {
            if (in_array($sheet, $sheetsWithMeetings, true)) {
                return true;
            }
        }

        return false;
    }
}
