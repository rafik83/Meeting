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
     * @var Participant
     */
    private $participant;

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
        $this->schedule    = $schedule;
        $this->participant = $participant;
        $this->begin       = $begin;
        $this->end         = $end;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get schedule.
     *
     * @return Schedule
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * Get participant.
     *
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * Get begin.
     *
     * @return \DateTime
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Get end.
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param Unavailability $unavailability
     */
    public function merge(Unavailability $unavailability)
    {
        if ($unavailability->getBegin() < $this->getBegin()) {
            $this->begin = $unavailability->getBegin();
        }

        if ($unavailability->getEnd() > $this->getEnd()) {
            $this->end = $unavailability->getEnd();
        }
    }

    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function update(\DateTime $begin, \DateTime $end)
    {
        $this->begin = $begin;
        $this->end   = $end;
    }
}
