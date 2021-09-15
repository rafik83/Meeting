<?php

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

use Proximum\Vimeet\Domain\Happening\HappeningDateHelper;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

abstract class AbstractSlotView
{
    /**
     * @var int
     */
    public $id;

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

    /**
     * @var string
     */
    public $type;

    /**
     * AbstractSlotView constructor.
     *
     * @param MeetingSlot $slot
     * @param string      $type
     */
    public function __construct(MeetingSlot $slot, $type)
    {
        $this->id           = $slot->getId();
        $this->begin        = $slot->getBegin();
        $this->end          = $slot->getEnd();
        $this->beginEndHour = $this->getFormattedHour($slot);
        $this->type         = $type;
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
    public function isEmptySlot()
    {
        return $this instanceof EmptySlotView;
    }

    /**
     * @return bool
     */
    public function isHappeningUnavailabilitySlot()
    {
        return $this instanceof HappeningUnavailabilitySlotView;
    }

    /**
     * @return bool
     */
    public function isMeetingSlot()
    {
        return $this instanceof MeetingSlotView;
    }

    /**
     * @return bool
     */
    public function isUnavailabilitySlot()
    {
        return $this instanceof UnavailabilitySlotView;
    }
}
