<?php

namespace Proximum\Vimeet\Domain\Meeting\Slot;

class Recipe
{
    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var int
     */
    public $interval;

    /**
     * @var int
     */
    public $duration;

    /**
     * Recipe constructor.
     *
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param int                $interval
     * @param int                $duration
     */
    public function __construct(\DateTimeInterface $begin, \DateTimeInterface $end, $interval, $duration)
    {
        $this->begin    = $begin;
        $this->end      = $end;
        $this->interval = $interval;
        $this->duration = $duration;
    }
}
