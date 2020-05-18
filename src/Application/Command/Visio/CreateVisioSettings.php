<?php

namespace Proximum\Vimeet\Application\Command\Visio;

use Proximum\Vimeet\Domain\Model\Event;

class CreateVisioSettings
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
