<?php

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Domain\Model\Sheet;

class SortedSheet
{
    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesserCache;

    /**
     * @param SheetInfoGuesserCache $sheetInfoGuesserCache
     */
    public function __construct(SheetInfoGuesserCache $sheetInfoGuesserCache)
    {
        $this->sheetInfoGuesserCache = $sheetInfoGuesserCache;
    }

    /**
     * @param Sheet[] $sheets
     *
     * @return Sheet[]
     */
    public function sort(array $sheets)
    {
        $sheetsWithTitle = [];

        foreach ($sheets as $sheet) {
            $sheetsWithTitle[] = [
                'title' => $this->sheetInfoGuesserCache->guessSheetTitle($sheet, null),
                'sheet' => $sheet,
            ];
        }

        usort($sheetsWithTitle, function ($sheetOne, $sheetTwo) {
            return strcasecmp($sheetOne['title'], $sheetTwo['title']);
        });

        $sheets = array_map(function ($sheetWithTitle) {
            return $sheetWithTitle['sheet'];
        }, $sheetsWithTitle);

        return $sheets;
    }
}
