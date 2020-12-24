<?php

namespace Proximum\Vimeet\Domain\Model\Event;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

class Day implements TimeRangeInterface
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
     * @var DateTimeInterface
     */
    private $startTime;

    /**
     * @var DateTimeInterface
     */
    private $endTime;

    /**
     * @param Event             $event
     * @param DateTimeInterface $startTime
     * @param DateTimeInterface $endTime
     */
    public function __construct(
        Event $event,
        DateTimeInterface $startTime,
        DateTimeInterface $endTime
    ) {
        $this->event     = $event;
        $this->startTime = $startTime;
        $this->endTime   = $endTime;
    }

    /**
     * @return int
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
     * @return DateTimeInterface
     */
    public function getDay()
    {
        return $this->startTime;
    }

    /**
     * @return DateTimeInterface
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return DateTimeInterface
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param DateTimeInterface $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @param DateTimeInterface $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getBegin()
    {
        return $this->getStartTime();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEnd()
    {
        return $this->getEndTime();
    }
}
