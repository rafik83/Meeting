<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Slot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class SlotPlus10minutes
{
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
        $dateTimePlus10Minutes = (clone $this->datetime)->modify('+10 min');

        $slots = array_filter($slots,
            function (MeetingSlot $slot) use ($dateTimePlus10Minutes) {
                return $slot->getBegin() >= $dateTimePlus10Minutes;
            }
        );

        return $slots;
    }
}
