<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Schedule;

class Add
{
    /**
     * @var Schedule
     */
    public $schedule;

    /**
     * @var Participant[]
     */
    public $participants = [];

    /**
     * @var \DateTime
     */
    public $from;

    /**
     * @var \DateTime
     */
    public $to;

    /**
     * AddUnavailability constructor.
     *
     * @param Schedule $schedule
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
        $this->from = clone $schedule->getDate();
        $this->to = clone $schedule->getDate();
    }
}
