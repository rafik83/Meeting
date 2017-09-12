<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Spot;

use Proximum\Vimeet\Domain\View\Spot\Import\SheetView;

class Import
{
    /** @var string */
    private $reference;

    /** @var int */
    private $size;

    /** @var int */
    private $meetingCapacity;

    /** @var int */
    private $seatCapacity;

    /** @var bool */
    private $active;

    /** @var int */
    private $priority;

    /** @var bool */
    private $visio;

    /** @var SheetView[] */
    private $sheets;

    /**
     * @param string      $reference
     * @param int         $size
     * @param int         $meetingCapacity
     * @param int         $seatCapacity
     * @param bool        $active
     * @param int         $priority
     * @param bool        $visio
     * @param SheetView[] $sheets
     */
    public function __construct(
        string $reference,
        int $size,
        int $meetingCapacity,
        int $seatCapacity,
        bool $active,
        int $priority,
        bool $visio,
        array $sheets
    ) {
        $this->reference = $reference;
        $this->size = $size;
        $this->meetingCapacity = $meetingCapacity;
        $this->seatCapacity = $seatCapacity;
        $this->active = $active;
        $this->priority = $priority;
        $this->visio = $visio;
        $this->sheets = $sheets;
    }
}
