<?php

namespace Proximum\Vimeet\Application\Query\Package\Payment;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;

class InfoViewQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    public function __construct(Sheet $sheet, string $locale)
    {
        $this->sheet = $sheet;
        $this->locale = $locale;
    }
}
