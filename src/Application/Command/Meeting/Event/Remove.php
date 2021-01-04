<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;

class Remove implements Command
{
    /** @var Sheet */
    public $sheet;

    /** @var Meeting */
    public $meeting;

    /** @var string */
    public $content;

    public function __construct(Sheet $sheet, Meeting $meeting)
    {
        $this->sheet = $sheet;
        $this->meeting = $meeting;
    }
}
