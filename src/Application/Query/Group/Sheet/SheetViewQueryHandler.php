<?php

namespace Proximum\Vimeet\Application\Query\Group\Sheet;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\View\Group\Sheet\SheetView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesser;

    /**
     * SheetViewQueryHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetInfoGuesserCache    $sheetInfoGuesser
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, SheetInfoGuesserCache $sheetInfoGuesser)
    {
        $this->sheetRepository  = $sheetRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param SheetViewQuery $sheetViewQuery
     *
     * @return SheetView[]
     */
    public function handle(SheetViewQuery $sheetViewQuery)
    {
        $sheetViews = [];
        $sheets     = $this->sheetRepository->getAllSheetsByUserAndEvent(
            $sheetViewQuery->user,
            $sheetViewQuery->event
        );

        foreach ($sheets as $sheet) {
            if (!$sheet->hasGroup()) {
                $sheetViews[] = new SheetView(
                    $sheet->getId(),
                    $this->sheetInfoGuesser->guessSheetTitle($sheet, $sheetViewQuery->locale)
                );
            }
        }

        return $sheetViews;
    }
}
