<?php

namespace Proximum\Vimeet\Domain\Slot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class SlotFilter
{
    const DELAY_IN_MINUTES = 10;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
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

        $dateTimePlus10Minutes = (clone $this->dateTime)->modify('+' . self::DELAY_IN_MINUTES . 'min');

        return array_filter($slots,
            function (MeetingSlot $slot) use ($dateTimePlus10Minutes) {
                return $slot->getBegin() >= $dateTimePlus10Minutes;
            }
        );
    }
}
