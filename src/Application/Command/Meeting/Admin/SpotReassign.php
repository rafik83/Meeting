<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class SpotReassign implements Command
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
