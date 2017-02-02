<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Unavailability;

use Proximum\Vimeet\Domain\Model\Participant;

/**
 * Assignement of a time slot from a disptached mass unavailability to a participant.
 */
class MassAssignment
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Mass
     */
    private $mass;

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
     * @var bool
     */
    private $enabled = true;

    /**
     * MassAssignment constructor.
     *
     * @param Mass               $mass
     * @param Participant        $participant
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     */
    public function __construct(Mass $mass, Participant $participant, \DateTimeInterface $begin, \DateTimeInterface $end)
    {
        $this->mass        = $mass;
        $this->participant = $participant;
        $this->begin       = $begin;
        $this->end         = $end;
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
     * Get participant
     *
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * Get begin
     *
     * @return \DateTimeInterface
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Get end
     *
     * @return \DateTimeInterface
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return Mass
     */
    public function getMass()
    {
        return $this->mass;
    }
}
