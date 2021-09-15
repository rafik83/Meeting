<?php

namespace Proximum\Vimeet\Application\Query\Notification\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class CompleteTranslationViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    public function __construct(Sheet $sheet, $locale)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
    }
}
