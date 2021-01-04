<?php

namespace Proximum\Vimeet\Domain\Model\Unavailability;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

/**
 * Assignement of a time slot from a dispatched mass unavailability to a user.
 */
class MassAssignment implements TimeRangeInterface
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
     * @var User
     */
    private $user;

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
     * @param User               $user
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     */
    public function __construct(Mass $mass, User $user, \DateTimeInterface $begin, \DateTimeInterface $end)
    {
        $this->mass  = $mass;
        $this->user  = $user;
        $this->begin = $begin;
        $this->end   = $end;
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
     * @return User
     */
    public function getUser()
    {
        return $this->user;
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
     * @return bool
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

    /**
     * Enable the mass assignment
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Disable the mass assignment
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param bool               $enabled
     */
    public function update(\DateTimeInterface $begin, \DateTimeInterface $end, $enabled)
    {
        $this->begin   = $begin;
        $this->end     = $end;
        $this->enabled = $enabled;
    }
}
