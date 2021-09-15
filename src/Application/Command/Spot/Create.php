<?php

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;

class Create implements Command
{
    /**
     * @var string
     */
    public $reference;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var float
     */
    public $size;

    /**
     * @var int
     */
    public $meetingCapacity = 1;

    /**
     * @var int
     */
    public $seatCapacity = 3;

    /**
     * @var bool
     */
    public $active;

    /**
     * @var int
     */
    public $priority;

    /**
     * @var bool
     */
    public $visio;

    /**
     * Create constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event    = $event;
        $this->priority = Spot::PRIORITY_MUTUALIZE;
        $this->visio    = $event->getConfiguration()->isVisio();
    }
}
