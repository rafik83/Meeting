<?php

namespace Proximum\Vimeet\Application\Query\Event\Find;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Event\Find\MultipleSheetsFoundView;
use Proximum\Vimeet\Application\View\Event\Find\SheetFoundView;

class MultipleSheetFoundViewQueryHandler
{
    /** @var SheetInfoGuesser */
    private $guesser;

    /**
     * @param SheetInfoGuesser $guesser
     */
    public function __construct(SheetInfoGuesser $guesser)
    {
        $this->guesser = $guesser;
    }

    /**
     * @param MultipleSheetFoundViewQuery $query
     *
     * @return MultipleSheetsFoundView
     */
    public function handle(MultipleSheetFoundViewQuery $query)
    {
        $sheets = [];

        foreach ($query->sheets as $sheet) {
            $sheets[] = new SheetFoundView(
                $sheet->getEvent()->getId(),
                $sheet->getEvent()->getTitle(),
                $sheet->getId(),
                $this->guesser->guessSheetTitle($sheet, $sheet->getEvent()->getFallback())
            );
        }

        return new MultipleSheetsFoundView($query->numero, $sheets);
    }
}
