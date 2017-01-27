<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Spot;

class SpotView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $reference;

    /**
     * @var float
     */
    public $size;

    /**
     * @var int
     */
    public $meetingCapacity;

    /**
     * @var int
     */
    public $seatCapacity;

    /**
     * @var bool
     */
    public $active;

    /**
     * @var SheetView
     */
    public $sheets = [];

    /**
     * @var bool
     */
    public $hasUnavailability;

    /**
     * @param int    $id
     * @param string $reference
     * @param float  $size
     * @param int    $meetingCapacity
     * @param int    $seatCapacity
     * @param bool   $active
     * @param bool   $hasUnavailability
     */
    public function __construct($id, $reference, $size, $meetingCapacity, $seatCapacity, $active, $hasUnavailability)
    {
        $this->id                = $id;
        $this->reference         = $reference;
        $this->size              = $size;
        $this->meetingCapacity   = $meetingCapacity;
        $this->seatCapacity      = $seatCapacity;
        $this->active            = $active;
        $this->hasUnavailability = $hasUnavailability;
    }

    /**
     * @param SheetView $sheetView
     */
    public function addSheet(SheetView $sheetView)
    {
        $this->sheets[] = $sheetView;
    }
}
