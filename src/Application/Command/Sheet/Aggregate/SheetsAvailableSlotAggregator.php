<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Aggregate;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class SheetsAvailableSlotAggregator implements Command
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
