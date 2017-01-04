<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Query\Agenda\HappeningViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\MassUnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\UnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Slot\AbstractSlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\EmptySlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\HappeningUnavailabilitySlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\MeetingSlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\UnavailabilitySlotView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class SlotViewQueryHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * @var HappeningViewQueryHandler
     */
    private $happeningHandler;

    /**
     * @var UnavailabilityViewQueryHandler
     */
    private $unavailabilityHandler;

    /**
     * @var MassUnavailabilityViewQueryHandler
     */
    private $massHandler;

    /**
     * @var MeetingViewQueryHandler
     */
    private $meetingViewQueryHandler;

    /**
     * SlotViewQueryHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface     $meetingSlotRepository
     * @param HappeningViewQueryHandler          $happeningHandler
     * @param UnavailabilityViewQueryHandler     $unavailabilityHandler
     * @param MassUnavailabilityViewQueryHandler $massHandler
     * @param MeetingViewQueryHandler            $meetingViewQueryHandler
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        HappeningViewQueryHandler $happeningHandler,
        UnavailabilityViewQueryHandler $unavailabilityHandler,
        MassUnavailabilityViewQueryHandler $massHandler,
        MeetingViewQueryHandler $meetingViewQueryHandler
    ) {
        $this->meetingSlotRepository   = $meetingSlotRepository;
        $this->happeningHandler        = $happeningHandler;
        $this->unavailabilityHandler   = $unavailabilityHandler;
        $this->massHandler             = $massHandler;
        $this->meetingViewQueryHandler = $meetingViewQueryHandler;
    }

    /**
     * @param SlotViewQuery $query
     *
     * @return AbstractSlotView[]
     */
    public function handle(SlotViewQuery $query)
    {
        $slots = $this->meetingSlotRepository->findByEventAndDay($query->event, $query->day);

        $slotViews = [];

        foreach ($slots as $slot) {
            if ($this->hasHappening($slot, $query)) {
                $slotViews[] = new HappeningUnavailabilitySlotView($slot);
                continue;
            }

            if ($meeting = $this->hasMeeting($slot, $query)) {
                $slotViews[] = new MeetingSlotView(
                    $slot,
                    $meeting->getSpot(),
                    $meeting->getSheetMet($query->sheet),
                    $meeting->getId(),
                    $meeting->getRequest()->hasNoPreference($query->sheet)
                );
                continue;
            }

            if ($this->hasUnavailability($slot, $query) || $this->hasMassUnavailability($slot, $query)) {
                $slotViews[] = new UnavailabilitySlotView($slot);
                continue;
            }

            $slotViews[] = new EmptySlotView($slot);
        }

        return $slotViews;
    }

    /**
     * @param MeetingSlot   $slot
     * @param SlotViewQuery $query
     *
     * @return bool
     */
    public function hasUnavailability(MeetingSlot $slot, SlotViewQuery $query)
    {
        foreach ($query->unavailabilities as $unavailability) {
            if ($slot->getBegin() >= $unavailability->getBegin()
                && $slot->getBegin() <= $unavailability->getEnd()
            ) {
                return true;
            }

            if ($slot->getEnd() >= $unavailability->getBegin()
                && $slot->getEnd() <= $unavailability->getEnd()
            ) {
                return true;
            }

            if ($slot->getBegin() >= $unavailability->getBegin()
                && $slot->getEnd() <= $unavailability->getEnd()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param MeetingSlot   $slot
     * @param SlotViewQuery $query
     *
     * @return bool
     */
    public function hasMassUnavailability(MeetingSlot $slot, SlotViewQuery $query)
    {
        foreach ($query->masses as $mass) {
            if ($slot->getBegin() >= $mass->getBegin()
                && $slot->getBegin() <= $mass->getEnd()
            ) {
                return true;
            }

            if ($slot->getEnd() >= $mass->getBegin()
                && $slot->getEnd() <= $mass->getEnd()
            ) {
                return true;
            }

            if ($slot->getBegin() >= $mass->getBegin()
                && $slot->getEnd() <= $mass->getEnd()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param MeetingSlot   $slot
     * @param SlotViewQuery $query
     *
     * @return bool|Meeting
     */
    public function hasMeeting(MeetingSlot $slot, SlotViewQuery $query)
    {
        foreach ($query->meetings as $meeting) {
            if ($meeting->getSlot() === $slot) {
                return $meeting;
            }
        }

        return false;
    }

    /**
     * @param MeetingSlot   $slot
     * @param SlotViewQuery $query
     *
     * @return bool
     */
    public function hasHappening(MeetingSlot $slot, SlotViewQuery $query)
    {
        foreach ($query->happenings as $happening) {
            if ($slot->getBegin() >= $happening->getHappening()->getBegin()
                && $slot->getBegin() <= $happening->getHappening()->getEnd()
            ) {
                return true;
            }

            if ($slot->getEnd() >= $happening->getHappening()->getBegin()
                && $slot->getEnd() <= $happening->getHappening()->getEnd()
            ) {
                return true;
            }

            if ($slot->getBegin() >= $happening->getHappening()->getBegin()
                && $slot->getEnd() <= $happening->getHappening()->getEnd()
            ) {
                return true;
            }
        }

        return false;
    }
}
