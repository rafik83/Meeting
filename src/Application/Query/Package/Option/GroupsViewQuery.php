<?php

namespace Proximum\Vimeet\Application\Query\Package\Option;

use Proximum\Vimeet\Domain\Model\Sheet;

class GroupsViewQuery
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
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Sheet $sheet, $locale)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
    }
}
