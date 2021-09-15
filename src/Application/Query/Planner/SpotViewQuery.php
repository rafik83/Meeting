<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Domain\Model\Event;

class SpotViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var SheetView[]
     */
    public $sheets;

    /**
     * @var SlotView[]
     */
    public $slots;

    /**
     * @param Event       $event
     * @param SheetView[] $sheets
     * @param SlotView[]  $slots
     */
    public function __construct(Event $event, array $sheets, array $slots)
    {
        $this->event  = $event;
        $this->sheets = $sheets;
        $this->slots  = $slots;
    }
}
