<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetValidationViewQuery implements Query
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * SheetValidationViewQuery constructor.
     *
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Sheet $sheet, $locale)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
    }
}
