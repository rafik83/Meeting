<?php

namespace Proximum\Vimeet\Application\View\Sheet\Group\Participant;

use Proximum\Vimeet\Domain\Happening\HappeningDateHelper;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class SlotView
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
     * @var string
     */
    public $beginEndHour;

    /** @var bool */
    public $available = false;

    /**
     * @param MeetingSlot $slot
     */
    public function __construct(MeetingSlot $slot)
    {
        $this->begin        = $slot->getBegin();
        $this->end          = $slot->getEnd();
        $this->beginEndHour = $this->getFormattedHour($slot);
    }

    /**
     * Get slot date formatted like that "10:00 - 10:30"
     *
     * @param MeetingSlot $slot
     *
     * @return string
     */
    private function getFormattedHour(MeetingSlot $slot)
    {
        return sprintf(
            '%s - %s',
            HappeningDateHelper::getHour($slot->getBegin(), null, $slot->getEvent()->getTimeZone()),
            HappeningDateHelper::getHour($slot->getEnd(), null, $slot->getEvent()->getTimeZone())
        );
    }

    /**
     * @return bool
     */
    public function isAnAvailableSlot()
    {
        return $this->available;
    }
}
