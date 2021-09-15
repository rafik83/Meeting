<?php


namespace Proximum\Vimeet\Domain\Helper;

use Proximum\Vimeet\Application\View\Agenda\SheetMetView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class LinkedSheetsTitle
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param Sheet $userSheet
     * @param Sheet $sheetMet
     *
     * @return SheetMetView[]
     */
    public function getSheetMetViews(Sheet $userSheet, Sheet $sheetMet): array
    {
        if (!$sheetMet->hasLinkedSheets()) {
            return [new SheetMetView($sheetMet->getTitle(), false)];
        }

        $linkedSheetTitles = [];
        $linkedSheets = $sheetMet->getLinkedSheets();
        foreach ($linkedSheets->getSheets() as $otherSheet) {
            $isHighLighted = $this->requestRepository->hasApprovedMeetingRequest($userSheet, $otherSheet);
            $linkedSheetTitles[] = new SheetMetView($otherSheet->getTitle(), $isHighLighted);
        }

        return $linkedSheetTitles;
    }
}
