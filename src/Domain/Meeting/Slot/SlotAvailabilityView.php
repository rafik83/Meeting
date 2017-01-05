<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Meeting\Slot;

use Proximum\Vimeet\Domain\Model\Meeting;

class SlotAvailabilityView
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * SlotAvailabilityView constructor.
     *
     * @param string       $type
     * @param Meeting|null $meeting
     */
    public function __construct($type, Meeting $meeting = null)
    {
        $this->type    = $type;
        $this->meeting = $meeting;
    }
}
