<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;

class WelcomeViewQuery implements Query
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
