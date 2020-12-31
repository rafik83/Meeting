<?php

namespace Proximum\Vimeet\Application\View\Agenda;

class CancelAttendanceUnavailabilityView extends AbstractTimeEntityView
{
    /**
     * @var string
     */
    public $timeZone;

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string             $timeZone
     */
    public function __construct(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $timeZone
    ) {
        $this->begin    = $begin;
        $this->end      = $end;
        $this->timeZone = $timeZone;
    }
}
