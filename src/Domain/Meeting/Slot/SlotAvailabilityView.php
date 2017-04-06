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
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

class SlotAvailabilityView
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var Meeting|null
     */
    public $meeting;

    /**
     * @var MassAssignment|null
     */
    public $massAssignment;

    /**
     * SlotAvailabilityView constructor.
     *
     * @param string              $type
     * @param Meeting|null        $meeting
     * @param MassAssignment|null $massAssignment
     */
    public function __construct($type, Meeting $meeting = null, MassAssignment $massAssignment = null)
    {
        $this->type           = $type;
        $this->meeting        = $meeting;
        $this->massAssignment = $massAssignment;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return $this->type === SlotAvailability::SLOT_AVAILABLE;
    }
}
