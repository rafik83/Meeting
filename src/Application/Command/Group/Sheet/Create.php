<?php

namespace Proximum\Vimeet\Application\Command\Group\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;

class Create implements Command
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $title;
}
