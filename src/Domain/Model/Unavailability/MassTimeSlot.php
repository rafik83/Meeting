<?php

namespace Proximum\Vimeet\Domain\Model\Unavailability;

use Proximum\Vimeet\Domain\Exception\Unavailability\InvalidTimeSlotException;

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
        if ($from >= $to) {
            throw new InvalidTimeSlotException('From date must be lesser than to date.');
        }

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
     * Get mass
     *
     * @return Mass
     */
    public function getMass()
    {
        return $this->mass;
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
    public function setFrom(\DateTimeInterface $from)
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
    public function setTo(\DateTimeInterface $to)
    {
        $this->to = $to;

        return $this;
    }
}
