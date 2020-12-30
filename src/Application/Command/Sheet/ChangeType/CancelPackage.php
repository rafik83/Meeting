<?php

namespace Proximum\Vimeet\Application\Command\Sheet\ChangeType;

use Proximum\Vimeet\Domain\Model\Sheet;

class CancelPackage
{
    /** @var Sheet */
    public $sheet;

    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
