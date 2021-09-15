<?php

namespace Proximum\Vimeet\Application\Command\Aggregate\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class FullUnavailability implements Command
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var bool
     */
    public $onlyCatalog;

    /**
     * @param Event $event
     * @param bool  $onlyCatalog
     */
    public function __construct(Event $event, $onlyCatalog = false)
    {
        $this->event = $event;
        $this->onlyCatalog = $onlyCatalog;
    }
}
