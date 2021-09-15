<?php

namespace Proximum\Vimeet\Application\Command\OMZ;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class PrepareContent implements Command
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
