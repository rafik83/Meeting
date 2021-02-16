<?php

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Application\View\Agenda\Slot\AbstractSlotView;

class AgendaDayView
{
    /**
     * @var AbstractSlotView[]
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
     * @param AbstractSlotView[] $slots
     */
    public function __construct($day, array $slots)
    {
        $this->slots = $slots;
        $this->day   = $day;
    }
}
