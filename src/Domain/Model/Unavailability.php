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
     * @var Participant
     */
    private $participant;

    /**
     * @var \DateTimeInterface
     */
    private $begin;

    /**
     * @var \DateTimeInterface
     */
    private $end;

    /**
     * Unavailability constructor.
     *
     * @param Participant        $participant
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     */
    public function __construct(Participant $participant, \DateTimeInterface $begin, \DateTimeInterface $end)
    {
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
     * @return \DateTimeInterface
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Get end.
     *
     * @return \DateTimeInterface
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
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     */
    public function update(\DateTimeInterface $begin, \DateTimeInterface $end)
    {
        $this->begin = $begin;
        $this->end   = $end;
    }
}
