<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\View;

class LeniPlanningView
{
    /** @var LeniPlanningDayView[] */
    public $days;

    /** @var string */
    public $unallocated;

    /**
     * @param LeniPlanningDayView[] $days
     * @param string                $unallocated
     */
    public function __construct(array $days, string $unallocated)
    {
        $this->days = $days;
        $this->unallocated = $unallocated;
    }
}
