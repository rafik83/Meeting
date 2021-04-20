<?php

namespace Proximum\Vimeet\Application\Command\Product\Import;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Import implements Command
{
    /**
     * Event to import the products
     *
     * @var Event
     */
    public $toEvent;

    /**
     * Event with the products to import
     *
     * @var Event
     */
    public $event;

    /**
     * @param Event $toEvent
     */
    public function __construct(Event $toEvent)
    {
        $this->toEvent = $toEvent;
    }
}
