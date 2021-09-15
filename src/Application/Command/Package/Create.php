<?php

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Create implements Command
{
    /** @var string */
    public $title;

    /** @var Event */
    public $event;

    public function __construct(Event $event = null)
    {
        $this->event = $event;
    }
}
