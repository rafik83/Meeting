<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class Unavailability
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Schedule
     */
    private $schedule;

    /**
     * @var \DateTime
     */
    private $begin;

    /**
     * @var \DateTime
     */
    private $end;

    /**
     * Unavailability constructor.
     *
     * @param Schedule    $schedule
     * @param Participant $participant
     * @param \DateTime   $begin
     * @param \DateTime   $end
     */
    public function __construct(Schedule $schedule, Participant $participant, \DateTime $begin, \DateTime $end)
    {
        $this->schedule = $schedule;
        $this->begin    = $begin;
        $this->end      = $end;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get schedule
     *
     * @return Schedule
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * Get begin
     *
     * @return \DateTime
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Get end
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }
}
