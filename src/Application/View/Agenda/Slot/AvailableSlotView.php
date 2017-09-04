<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

class AvailableSlotView
{
    /** @var int */
    public $id;

    /** @var \DateTimeInterface */
    public $beginHour;

    /** @var string */
    public $duration;

    /**
     * @param int                $id
     * @param \DateTimeInterface $beginHour
     * @param string             $duration
     */
    public function __construct(int $id, \DateTimeInterface $beginHour, string $duration)
    {
        $this->id = $id;
        $this->beginHour = $beginHour;
        $this->duration = $duration;
    }
}
