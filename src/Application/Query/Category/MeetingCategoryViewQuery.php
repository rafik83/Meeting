<?php

namespace Proximum\Vimeet\Application\Query\Category;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingCategoryViewQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /**
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Sheet $sheet, string $locale)
    {
        $this->sheet = $sheet;
        $this->locale = $locale;
    }
}
