<?php

namespace Proximum\Vimeet\Application\View\Sheet\Group\Participant;

class AgendaDayView
{
    /**
     * @var SlotView[]
     */
    public $slots;

    /**
     * Day number incremented
     *
     * @var int
     */
    public $day;

    /**
     * AgendaDayView constructor.
     *
     * @param int                $day
     * @param SlotView[] $slots
     */
    public function __construct($day, array $slots)
    {
        $this->slots = $slots;
        $this->day   = $day;
    }
}
