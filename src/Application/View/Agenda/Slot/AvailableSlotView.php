<?php

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

class AvailableSlotView
{
    /** @var int */
    public $id;

    /** @var \DateTimeInterface */
    public $beginHour;

    /** @var \DateTimeInterface */
    public $endHour;

    /**
     * @param int                $id
     * @param \DateTimeInterface $beginHour
     * @param \DateTimeInterface $endHour
     */
    public function __construct(int $id, \DateTimeInterface $beginHour, \DateTimeInterface $endHour)
    {
        $this->id = $id;
        $this->beginHour = $beginHour;
        $this->endHour = $endHour;
    }

    /**
     * @return \DateInterval
     */
    public function getDuration(): \DateInterval
    {
        return $this->endHour->diff($this->beginHour);
    }
}
