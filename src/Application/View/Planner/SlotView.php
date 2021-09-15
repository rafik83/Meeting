<?php

namespace Proximum\Vimeet\Application\View\Planner;

class SlotView
{
    /** @var int */
    public $id;

    /** @var int */
    public $index;

    /** @var int */
    public $hour;

    /** @var int */
    public $minute;

    /** @var Day */
    public $day;

    /** @var string */
    public $reference;

    /**
     * @param int $id
     * @param int $index
     * @param int $hour
     * @param int $minute
     * @param Day $day
     */
    public function __construct(
        $id,
        $index,
        $hour,
        $minute,
        Day $day
    ) {
        $this->id        = $id;
        $this->index     = $index;
        $this->hour      = $hour;
        $this->minute    = $minute;
        $this->day       = $day;
        $this->reference = sprintf('slot%s', $id);
    }
}
