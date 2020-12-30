<?php

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Event;

class UnArchive
{
    /** @var Event */
    public $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
