<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;

class TemplateObjectViewQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /** @var string */
    public $key;

    /**
     * @param Sheet  $sheet
     * @param string $locale
     * @param string $key
     */
    public function __construct(Sheet $sheet, $locale, $key)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->key    = $key;
    }
}
