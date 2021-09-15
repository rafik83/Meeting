<?php

namespace Proximum\Vimeet\Application\Command\Planning;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetInfoGuesserCache
{
    /**
     * @var SheetInfoGuesser
     */
    private $guesser;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param SheetInfoGuesser $guesser
     */
    public function __construct(SheetInfoGuesser $guesser)
    {
        $this->guesser = $guesser;
    }

    /**
     * @param Sheet       $sheet
     * @param string|null $locale
     *
     * @return string
     */
    public function guessSheetTitle(Sheet $sheet, $locale = null)
    {
        $key = $sheet->getId() . $locale;

        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $this->guesser->guessSheetTitle($sheet, $locale);
        }

        return $this->cache[$key];
    }
}
