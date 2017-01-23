<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planner;

class SpotView
{
    /** @var int */
    public $id;

    /** @var string */
    public $spotReference;

    /** @var int */
    public $seatCapacity;

    /** @var int */
    public $meetingCapacity;

    /** @var SheetView[] */
    public $sheetList;

    /** @var int */
    public $priority;

    /** @var string */
    public $reference;

    /**
     * @param int         $id
     * @param string      $spotReference
     * @param int         $seatCapacity
     * @param int         $meetingCapacity
     * @param SheetView[] $sheetList
     * @param int         $priority
     */
    public function __construct(
        $id,
        $spotReference,
        $seatCapacity,
        $meetingCapacity,
        array $sheetList = [],
        $priority
    ) {
        $this->id              = $id;
        $this->spotReference   = $spotReference;
        $this->seatCapacity    = $seatCapacity;
        $this->meetingCapacity = $meetingCapacity;
        $this->sheetList       = $sheetList;
        $this->priority        = $priority;
        $this->reference       = sprintf('spot%s', $id);
    }

    /**
     * @return array
     */
    public function getSheets()
    {
        return array_map(function (SheetView $sheetView) {
            return $sheetView->id;
        }, $this->sheetList);
    }
}
