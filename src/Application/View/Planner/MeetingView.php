<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planner;

use Proximum\Vimeet\Application\Exception\Planner\Meeting\NoLockedSlotException;
use Proximum\Vimeet\Application\Exception\Planner\Meeting\NoLockedSpotException;
use Proximum\Vimeet\Application\Exception\Planner\Meeting\NoSlotException;
use Proximum\Vimeet\Application\Exception\Planner\Meeting\NoSpotException;

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
     * Info if the slot was locked or not for the import
     * @var bool
     */
    public $blockedSlot;

    /**
     * Info if the spot was locked or not for the import
     * @var bool
     */
    public $blockedSpot;

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
        $this->blockedSpot     = false;
        $this->blockedSlot     = false;
    }

    /**
     * @return bool
     */
    public function hasSlot()
    {
        return $this->slot !== null;
    }

    /**
     * @return string
     * @throws NoSlotException
     */
    public function getSlotReference()
    {
        if ($this->slot === null) {
            throw new NoSlotException();
        }

        return $this->slot->reference;
    }

    /**
     * @return string
     * @throws NoSpotException
     */
    public function getSpotReference()
    {
        if ($this->spot === null) {
            throw new NoSpotException();
        }

        return $this->spot->reference;
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
     * @return string
     * @throws NoLockedSlotException
     */
    public function getLockedSlotReference()
    {
        if ($this->lockedSlot === null) {
            throw new NoLockedSlotException();
        }

        return $this->lockedSlot->reference;
    }

    /**
     * @return bool
     */
    public function hasLockedSpot()
    {
        return $this->lockedSpot !== null;
    }

    /**
     * @return string
     * @throws NoLockedSpotException
     */
    public function getLockedSpotReference()
    {
        if ($this->lockedSpot === null) {
            throw new NoLockedSpotException();
        }

        return $this->lockedSpot->reference;
    }
}
