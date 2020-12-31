<?php

namespace Proximum\Vimeet\Domain\Slot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class SlotFilter
{
    const DELAY_IN_MINUTES = 10;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @param \DateTimeInterface $datetime
     */
    public function __construct(\DateTimeInterface $datetime)
    {
        $this->datetime = $datetime;
    }

    /**
     * @param MeetingSlot[] $slots
     *
     * @return MeetingSlot[]
     */
    public function getFilteredSlots(array $slots): array
    {
        if (empty($slots)) {
            return [];
        }

        $dateTimePlus10Minutes = (clone $this->datetime)->modify('+' . self::DELAY_IN_MINUTES . 'min');

        return array_filter($slots,
            function (MeetingSlot $slot) use ($dateTimePlus10Minutes) {
                return $slot->getBegin() >= $dateTimePlus10Minutes;
            }
        );
    }
}
