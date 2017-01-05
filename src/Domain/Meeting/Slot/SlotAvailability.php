<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Meeting\Slot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class SlotAvailability
{
    const HAPPENING_UNAVAILABILITY = 'happening_unavailability';
    const UNAVAILABILITY           = 'unavailability';
    const MEETING_UNAVAILABILITY   = 'meeting_unavailability';
    const MASS_UNAVAILABILITY      = 'mass_unavailability';
    const SLOT_AVAILABLE           = 'slot_available';

    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * @var MassRepositoryInterface
     */
    private $massUnavailabilityRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepositoryInterface;

    /**
     * @var HappeningParticipation[]
     */
    private $happenings = null;

    /**
     * @var Meeting[]
     */
    private $meetings = null;

    /**
     * @var Unavailability[]
     */
    private $unavailability = null;

    /**
     * @var Mass[]
     */
    private $massUnavailability = null;

    /**
     * SlotAvailability constructor.
     *
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param MassRepositoryInterface                   $massUnavailabilityRepository
     * @param MeetingRepositoryInterface                $meetingRepositoryInterface
     */
    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        MassRepositoryInterface $massUnavailabilityRepository,
        MeetingRepositoryInterface $meetingRepositoryInterface
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
        $this->massUnavailabilityRepository     = $massUnavailabilityRepository;
        $this->meetingRepositoryInterface       = $meetingRepositoryInterface;
    }

    /**
     * @param HappeningParticipation[] $happenings
     * @param Meeting[]                $meetings
     * @param Unavailability[]         $unavailability
     * @param Mass[]                   $massUnavailability
     */
    public function preload(
        array $happenings = [],
        array $meetings = [],
        array $unavailability = [],
        array $massUnavailability = []
    ) {
        $this->happenings         = $happenings;
        $this->meetings           = $meetings;
        $this->unavailability     = $unavailability;
        $this->massUnavailability = $massUnavailability;
    }

    /**
     * @param MeetingSlot $slot
     * @param Participant $participant
     *
     * @return SlotAvailabilityView
     */
    public function isAvailable(MeetingSlot $slot, Participant $participant)
    {
        $this->autoLoading($participant->getSheet()->getEvent());

        if ($this->hasUnavailability($slot, $participant)) {
            return new SlotAvailabilityView(self::UNAVAILABILITY);
        }

        if ($this->hasHappening($slot, $participant)) {
            return new SlotAvailabilityView(self::HAPPENING_UNAVAILABILITY);
        }

        if ($this->hasMassUnavailability($slot)) {
            return new SlotAvailabilityView(self::MASS_UNAVAILABILITY);
        }

        if (($meeting = $this->hasMeeting($slot, $participant)) !== false) {
            return new SlotAvailabilityView(self::MEETING_UNAVAILABILITY, $meeting);
        }

        return new SlotAvailabilityView(self::SLOT_AVAILABLE);
    }

    /**
     * Autoload if preload was not used
     *
     * @param Event $event
     */
    private function autoLoading(Event $event)
    {
        if ($this->happenings === null) {
            $this->happenings = $this->happeningParticipationRepository->findByEvent($event);
        }

        if ($this->meetings === null) {
            $this->meetings = $this->meetingRepositoryInterface->findByEvent($event);
        }

        if ($this->unavailability === null) {
            $this->unavailability = $this->unavailabilityRepository->findByEvent($event);
        }

        if ($this->massUnavailability === null) {
            $this->massUnavailability = $this->massUnavailabilityRepository->findByEvent($event);
        }
    }

    /**
     * @param MeetingSlot $slot
     * @param Participant $participant
     *
     * @return bool
     */
    private function hasUnavailability(MeetingSlot $slot, Participant $participant)
    {
        foreach ($this->unavailability as $unavailability) {
            if ($unavailability->getParticipant() !== $participant) {
                continue;
            }

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
     * @param MeetingSlot $slot
     *
     * @return bool
     */
    private function hasMassUnavailability(MeetingSlot $slot)
    {
        foreach ($this->massUnavailability as $mass) {
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
     * @param MeetingSlot $slot
     * @param Participant $participant
     *
     * @return Meeting|false
     */
    private function hasMeeting(MeetingSlot $slot, Participant $participant)
    {
        foreach ($this->meetings as $meeting) {
            if (!$meeting->hasFromParticipant($participant) || !$meeting->hasToParticipant($participant)) {
                continue;
            }
            
            if ($meeting->getSlot() === $slot) {
                return $meeting;
            }
        }

        return false;
    }

    /**
     * @param MeetingSlot $slot
     * @param Participant $participant
     *
     * @return bool
     */
    private function hasHappening(MeetingSlot $slot, Participant $participant)
    {
        foreach ($this->happenings as $happening) {
            if ($happening->getParticipant() !== $participant) {
                continue;
            }

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
