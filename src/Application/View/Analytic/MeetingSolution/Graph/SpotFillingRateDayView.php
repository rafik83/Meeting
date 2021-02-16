<?php

namespace Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph;

class SpotFillingRateDayView
{
    /** @var \DateTimeInterface */
    public $date;

    /** @var SpotFillingRateSlotView[] */
    public $slotsFillingRate;

    /** @var string */
    public $timeZone;

    /**
     * @param \DateTimeInterface $date
     * @param string             $timeZone
     */
    public function __construct(\DateTimeInterface $date, string $timeZone)
    {
        $this->date = $date;
        $this->slotsFillingRate = [];
        $this->timeZone = $timeZone;
    }

    /**
     * @param SpotFillingRateSlotView $spotFillingRateSlot
     */
    public function addSlotFillingRate(SpotFillingRateSlotView $spotFillingRateSlot)
    {
        $this->slotsFillingRate[] = $spotFillingRateSlot;
    }
}
