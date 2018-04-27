<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Domain\Model\Event\Day;

class SlotViewsQuery
{
    /** @var Day */
    public $day;

    /**
     * SlotViewsQuery constructor.
     *
     * @param Day $day
     */
    public function __construct(Day $day)
    {
        $this->day = $day;
    }
}
