<?php

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

class MeetingSlot implements TimeRangeInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

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
    private $locked = false;

    /**
     * MeetingSlot constructor.
     *
     * @param Event              $event
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param bool               $locked
     */
    public function __construct(Event $event, \DateTimeInterface $begin, \DateTimeInterface $end, $locked = false)
    {
        $this->event   = $event;
        $this->begin   = $begin;
        $this->end     = $end;
        $this->locked  = $locked;
    }

    /**
     * Get id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param \DateTimeInterface $begin
     *
     * @return MeetingSlot
     */
    public function setBegin(\DateTimeInterface $begin)
    {
        $this->begin = $begin;

        return $this;
    }

    /**
     * @param \DateTimeInterface $end
     *
     * @return MeetingSlot
     */
    public function setEnd(\DateTimeInterface $end)
    {
        $this->end = $end;

        return $this;
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
     * Get locked
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @return MeetingSlot
     */
    public function lock()
    {
        $this->locked = true;

        return $this;
    }

    /**
     * @return MeetingSlot
     */
    public function unlock()
    {
        $this->locked = false;

        return $this;
    }

    /**
     * @return string
     */
    public function duration()
    {
        return ($this->end->getTimestamp() - $this->begin->getTimestamp()) / 60;
    }
}
