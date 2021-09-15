<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\View;

class LeniPlanningDayView
{
    /** @var string */
    public $planning;

    /**
     * @param string $planning
     */
    public function __construct(string $planning)
    {
        $this->planning = $planning;
    }
}
