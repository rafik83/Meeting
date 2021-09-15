<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class CreateForEvent implements Command
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var Event
     */
    public $event;
}
