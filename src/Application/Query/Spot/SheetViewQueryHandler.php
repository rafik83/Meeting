<?php

namespace Proximum\Vimeet\Application\Query\Spot;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Spot\SheetView;

class SheetViewQueryHandler
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @param SheetInfoGuesser $sheetInfoGuesser
     */
    public function __construct(SheetInfoGuesser $sheetInfoGuesser)
    {
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param SheetViewQuery $query
     *
     * @return SheetView
     */
    public function handle(SheetViewQuery $query)
    {
        return new SheetView(
            $query->sheet->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($query->sheet, $query->locale)
        );
    }
}
