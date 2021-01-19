<?php

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Domain\Model\Order\Row;

class CustomRowViewQuery
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var Row
     */
    public $row;

    /**
     * @param Row    $row
     * @param string $locale
     */
    public function __construct(Row $row, $locale)
    {
        $this->row    = $row;
        $this->locale = $locale;
    }
}
