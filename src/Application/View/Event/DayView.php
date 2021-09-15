<?php

namespace Proximum\Vimeet\Application\View\Event;

class DayView
{
    /**
     * @var \DateTimeInterface
     */
    public $startTime;

    /**
     * @var \DateTimeInterface
     */
    public $endTime;

    /**
     * DayView constructor.
     *
     * @param \DateTimeInterface $startTime
     * @param \DateTimeInterface $endTime
     */
    public function __construct(\DateTimeInterface $startTime, \DateTimeInterface $endTime)
    {
        $this->startTime = $startTime;
        $this->endTime   = $endTime;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->startTime->format('d/m/Y');
    }
}
