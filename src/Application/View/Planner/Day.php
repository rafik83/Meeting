<?php

namespace Proximum\Vimeet\Application\View\Planner;

class Day
{
    /** @var int */
    public $id;

    /** @var int */
    public $day;

    /** @var int */
    public $month;

    /** @var int */
    public $year;

    /** @var string */
    public $reference;

    /**
     * @param int $id
     * @param int $day
     * @param int $month
     * @param int $year
     */
    public function __construct($id, $day, $month, $year)
    {
        $this->id        = $id;
        $this->day       = $day;
        $this->month     = $month;
        $this->year      = $year;
        $this->reference = sprintf('day%s', $id);
    }
}
