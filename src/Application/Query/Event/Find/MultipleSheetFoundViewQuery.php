<?php

namespace Proximum\Vimeet\Application\Query\Event\Find;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;

class MultipleSheetFoundViewQuery implements Query
{
    /** @var Sheet[] */
    public $sheets;

    /** @var string */
    public $numero;

    /**
     * @param string  $numero
     * @param Sheet[] $sheets
     */
    public function __construct($numero, array $sheets)
    {
        $this->numero = $numero;
        $this->sheets = $sheets;
    }
}
