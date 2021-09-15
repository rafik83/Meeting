<?php

namespace Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph;

class SpotFillingRateSlotView
{
    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

    /** @var int */
    public $fillingRate;

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param int                $fillingRate
     */
    public function __construct(\DateTimeInterface $begin, \DateTimeInterface $end, int $fillingRate)
    {
        $this->begin = $begin;
        $this->end = $end;
        $this->fillingRate = $fillingRate;
    }
}
