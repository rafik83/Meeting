<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Spot;

class Import
{
    /** @var string */
    public $reference;

    /** @var int */
    public $size;

    /** @var int */
    public $meetingCapacity;

    /** @var int */
    public $seatCapacity;

    /** @var bool */
    public $active;

    /** @var int */
    public $priority;

    /** @var bool */
    public $visio;

    /**
     * @param string      $reference
     * @param int         $size
     * @param int         $meetingCapacity
     * @param int         $seatCapacity
     * @param bool        $active
     * @param int         $priority
     * @param bool        $visio
     */
    public function __construct(
        string $reference,
        int $size,
        int $meetingCapacity,
        int $seatCapacity,
        bool $active,
        int $priority,
        bool $visio
    ) {
        $this->reference = $reference;
        $this->size = $size;
        $this->meetingCapacity = $meetingCapacity;
        $this->seatCapacity = $seatCapacity;
        $this->active = $active;
        $this->priority = $priority;
        $this->visio = $visio;
    }
}
