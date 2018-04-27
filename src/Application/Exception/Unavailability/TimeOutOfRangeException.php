<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Unavailability;

use Proximum\Vimeet\Domain\Model\Event\Day;

class TimeOutOfRangeException extends UnavailabilityException
{
    const BEGIN = 'begin';
    const END   = 'end';

    /**
     * @var Day
     */
    public $day;

    /**
     * @var string
     */
    public $period;

    /**
     * @param Day    $day
     * @param string $period
     */
    public function __construct(Day $day, $period)
    {
        parent::__construct('Time selected is out of range');
        $this->day    = $day;
        $this->period = $period;
    }

    /**
     * @return bool
     */
    public function isOutOfRangeAtEndOfDay()
    {
        return self::END === $this->period;
    }

    /**
     * @return bool
     */
    public function isOutOfRangeAtBeginOfDay()
    {
        return self::BEGIN === $this->period;
    }
}
