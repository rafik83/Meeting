<?php

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Application\Components\Spot\Recipe;
use Proximum\Vimeet\Domain\Model\Event;

class BatchCreate
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Recipe[]
     */
    public $recipes;

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
     * BatchCreate constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event     = $event;
        $this->recipes[] = new Recipe('');
        $this->visio     = $event->getConfiguration()->isVisio();
        $this->priority  = 12;
    }
}
