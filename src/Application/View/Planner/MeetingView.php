<?php

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
     *
     * @var bool
     */
    public $isBlockedSlot;

    /**
     * Info if the spot was locked or not for the import
     *
     * @var bool
     */
    public $isBlockedSpot;

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
        $this->isBlockedSpot   = false;
        $this->isBlockedSlot   = false;
    }

    /**
     * @return bool
     */
    public function hasSlot()
    {
        return null !== $this->slot;
    }

    /**
     * @throws NoSlotException
     *
     * @return string
     */
    public function getSlotReference()
    {
        if (null === $this->slot) {
            throw new NoSlotException();
        }

        return $this->slot->reference;
    }

    /**
     * @throws NoSpotException
     *
     * @return string
     */
    public function getSpotReference()
    {
        if (null === $this->spot) {
            throw new NoSpotException();
        }

        return $this->spot->reference;
    }

    /**
     * @return bool
     */
    public function hasSpot()
    {
        return null !== $this->spot;
    }

    /**
     * @return bool
     */
    public function hasLockedSlot()
    {
        return null !== $this->lockedSlot;
    }

    /**
     * @throws NoLockedSlotException
     *
     * @return string
     */
    public function getLockedSlotReference()
    {
        if (null === $this->lockedSlot) {
            throw new NoLockedSlotException();
        }

        return $this->lockedSlot->reference;
    }

    /**
     * @return bool
     */
    public function hasLockedSpot()
    {
        return null !== $this->lockedSpot;
    }

    /**
     * @throws NoLockedSpotException
     *
     * @return string
     */
    public function getLockedSpotReference()
    {
        if (null === $this->lockedSpot) {
            throw new NoLockedSpotException();
        }

        return $this->lockedSpot->reference;
    }
}
