<?php

namespace Proximum\Vimeet\Application\Components\Planning\Formatter;

class FormattedPlanningView
{
    /** @var array of string */
    public $days;

    /** @var string */
    public $unallocated;

    /**
     * @param array  $days
     * @param string $unallocated
     */
    public function __construct(array $days, string $unallocated)
    {
        $this->days = $days;
        $this->unallocated = $unallocated;
    }
}
