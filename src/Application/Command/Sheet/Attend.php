<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;

class Attend implements Command
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var bool
     */
    public $attend;

    /**
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
        $this->attend = $sheet->attend();
    }
}
