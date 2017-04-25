<?php

namespace Proximum\Vimeet\Application\View\Sheet\Group\Participant;

class AgendaDayView
{
    /**
     * @var SlotView[]
     */
    public $slotViews;

    /**
     * Day number incremented
     *
     * @var int
     */
    public $day;

    /**
     * AgendaDayView constructor.
     *
     * @param int        $day
     * @param SlotView[] $slotViews
     */
    public function __construct($day, array $slotViews)
    {
        $this->slotViews = $slotViews;
        $this->day       = $day;
    }
}
