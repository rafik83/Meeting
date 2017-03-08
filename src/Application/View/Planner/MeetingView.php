<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planner;

class MeetingView
{
    /** @var int */
    public $id;

    /** @var SheetView[] */
    public $sheetList;

    /** @var ParticipantView[] */
    public $participantList;

    /** @var string */
    public $reference;

    /** @var bool */
    public $isVisio;

    /**
     * @var null|SpotView
     */
    public $spot = null;

    /**
     * @var null|SlotView
     */
    public $slot = null;

    /**
     * @var null|SpotView
     */
    public $lockedSpot = null;

    /**
     * @var null|SlotView
     */
    public $lockedSlot = null;

    /**
     * @param int               $id
     * @param SheetView[]       $sheetList
     * @param ParticipantView[] $participantList
     * @param bool              $isVisio
     */
    public function __construct($id, array $sheetList, array $participantList, $isVisio = false)
    {
        $this->id              = $id;
        $this->sheetList       = $sheetList;
        $this->participantList = $participantList;
        $this->isVisio         = $isVisio;
        $this->reference       = sprintf('meeting%s', $id);
    }

    /**
     * @return bool
     */
    public function hasSlot()
    {
        return $this->slot !== null;
    }

    /**
     * @return bool
     */
    public function hasSpot()
    {
        return $this->spot !== null;
    }

    /**
     * @return bool
     */
    public function hasLockedSlot()
    {
        return $this->lockedSlot !== null;
    }

    /**
     * @return bool
     */
    public function hasLockedSpot()
    {
        return $this->lockedSpot !== null;
    }
}
