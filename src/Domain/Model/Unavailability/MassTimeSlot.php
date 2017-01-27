<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Unavailability;

class MassTimeSlot
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
     * @var \DateTimeInterface
     */
    private $from;

    /**
     * @var \DateTimeInterface
     */
    private $to;

    /**
     * TimeSlot constructor.
     *
     * @param Mass               $mass
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     */
    public function __construct(Mass $mass, \DateTimeInterface $from, \DateTimeInterface $to)
    {
        $this->mass = $mass;
        $this->from = $from;
        $this->to   = $to;
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
     * Get from
     *
     * @return \DateTimeInterface
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set from
     *
     * @param \DateTimeInterface $from
     *
     * @return MassTimeSlot
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get to
     *
     * @return \DateTimeInterface
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set to
     *
     * @param \DateTimeInterface $to
     *
     * @return MassTimeSlot
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }
}
