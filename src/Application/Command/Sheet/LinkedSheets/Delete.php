<?php

namespace Proximum\Vimeet\Application\Command\Sheet\LinkedSheets;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;

class Delete implements Command
{
    /** @var LinkedSheets */
    public $linkedSheets;

    public function __construct(LinkedSheets $linkedSheets)
    {
        $this->linkedSheets = $linkedSheets;
    }
}
