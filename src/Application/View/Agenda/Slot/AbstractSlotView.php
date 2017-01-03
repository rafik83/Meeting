<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

abstract class AbstractSlotView
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
     * AbstractSlotView constructor.
     *
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     */
    public function __construct(\DateTimeInterface $begin, \DateTimeInterface $end)
    {
        $this->begin = $begin;
        $this->end   = $end;
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
