<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planner;

class ParticipantView
{
    /** @var int */
    public $id;

    /** @var string */
    public $fullName;

    /** @var SheetView */
    public $sheet;

    /** @var SlotView[] */
    public $unavailabilityList;

    /** @var string */
    public $reference;

    /**
     * @param int        $id
     * @param string     $fullName
     * @param SheetView  $sheet
     * @param SlotView[] $unavailabilityList
     */
    public function __construct($id, $fullName, SheetView $sheet, array $unavailabilityList)
    {
        $this->id                 = $id;
        $this->fullName           = $fullName;
        $this->sheet              = $sheet;
        $this->unavailabilityList = $unavailabilityList;
        $this->reference          = sprintf('participant%s', $id);
    }
}
