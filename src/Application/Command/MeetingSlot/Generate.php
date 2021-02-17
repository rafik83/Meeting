<?php

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Meeting\Slot\Recipe;
use Proximum\Vimeet\Domain\Model\Event;

class Generate implements Command
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Recipe[]
     */
    public $recipes = [];

    /**
     * Generate constructor.
     *
     * @param $event
     */
    public function __construct(Event $event)
    {
        $this->event     = $event;
        $this->recipes[] = new Recipe(new \DateTime(), new \DateTime(), 5, 25);
    }
}
