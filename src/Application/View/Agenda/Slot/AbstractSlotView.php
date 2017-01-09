<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

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
        $this->beginEndHour = $slot->getBegin()->format('H:i') . ' - ' . $slot->getEnd()->format('H:i');
        $this->type         = $type;
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
