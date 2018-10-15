<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Meeting\Slot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Domain\Time\TimeOverlap;

class SlotAvailability
{
    const HAPPENING_UNAVAILABILITY          = 'happening_unavailability';
    const UNAVAILABILITY                    = 'unavailability';
    const MEETING_UNAVAILABILITY            = 'meeting_unavailability';
    const MASS_UNAVAILABILITY               = 'mass_unavailability';
    const SLOT_AVAILABLE                    = 'slot_available';
    const MASS_ASSIGNMENT_UNAVAILABILITY    = 'mass_assignment_unavailability';
    const MEETING_ON_OTHER_SHEET            = 'meeting_on_other_sheet';

    const ASSIGNMENT_DISABLED   = 'disabled';
    const ASSIGNMENT_FOUND      = 'found';
    const ASSIGNMENT_NOT_FOUND  = 'not_found';

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
     * @var MassAssignmentRepositoryInterface
     */
    private $massAssignmentRepository;

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
     * @var MassAssignment[]
     */
    private $massAssignment = null;

    /**
     * @var Meeting[]
     */
    private $meetingOtherSheets = null;

    /**
     * Array of happeningParticipation [participantId][1 => happeningParticipation, 2 => happeningParticipation]
     *
     * @var array
     */
    private $happeningsSortByParticipant = [];

    /**
     * Array of meeting [participantId][1 => meeting, 2 => meeting]
     *
     * @var array
     */
    private $meetingsSortByParticipant = [];

    /**
     * Array of unavailability [userId][1 => unavailability, 2 => unavailability]
     *
     * @var array
     */
    private $unavailabilitySortByUser = [];

    /**
     * Array of mass assignment [UserId][1 => assignment, 2 => assignment]
     *
     * @var array
     */
    private $massAssignmentSortByUser = [];

    /**
     * SlotAvailability constructor.
     *
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param MassRepositoryInterface                   $massUnavailabilityRepository
     * @param MeetingRepositoryInterface                $meetingRepositoryInterface
     * @param MassAssignmentRepositoryInterface         $massAssignmentRepository
     */
    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        MassRepositoryInterface $massUnavailabilityRepository,
        MeetingRepositoryInterface $meetingRepositoryInterface,
        MassAssignmentRepositoryInterface $massAssignmentRepository
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->unavailabilityRepository         = $unavailabilityRepository;
        $this->massUnavailabilityRepository     = $massUnavailabilityRepository;
        $this->meetingRepositoryInterface       = $meetingRepositoryInterface;
        $this->massAssignmentRepository         = $massAssignmentRepository;
    }

    /**
     * @param HappeningParticipation[] $happenings
     * @param Meeting[]                $meetings
     * @param Unavailability[]         $unavailability
     * @param Mass[]                   $massUnavailability
     * @param MassAssignment[]         $massAssignments
     * @param Meeting[]                $meetingOtherSheets
     */
    public function preload(
        array $happenings = [],
        array $meetings = [],
        array $unavailability = [],
        array $massUnavailability = [],
        array $massAssignments = [],
        array $meetingOtherSheets = []
    ) {
        $this->happenings         = $happenings;
        $this->meetings           = $meetings;
        $this->unavailability     = $unavailability;
        $this->massUnavailability = $massUnavailability;
        $this->meetingOtherSheets = $meetingOtherSheets;

        $this->assignMeetingSortByParticipant($meetings);
        $this->assignHappeningSortByParticipant($happenings);
        $this->assignUnavailabilitySortByUser($unavailability);
        $this->assignMassAssignmentSortByUser($massAssignments);
    }

    /**
     * @param Meeting[] $meetings
     */
    private function assignMeetingSortByParticipant(array $meetings)
    {
        foreach ($meetings as $meeting) {
            foreach ($meeting->getAllParticipants() as $participant) {
                $this->meetingsSortByParticipant[$participant->getId()][] = $meeting;
            }
        }
    }

    /**
     * @param HappeningParticipation[] $happenings
     */
    private function assignHappeningSortByParticipant(array $happenings)
    {
        foreach ($happenings as $happening) {
            $this->happeningsSortByParticipant[$happening->getUser()->getId()][] = $happening;
        }
    }

    /**
     * @param Unavailability[] $unavailabilities
     */
    private function assignUnavailabilitySortByUser(array $unavailabilities)
    {
        foreach ($unavailabilities as $unavailability) {
            $this->unavailabilitySortByUser[$unavailability->getUser()->getId()][] = $unavailability;
        }
    }

    /**
     * @param MassAssignment[] $massAssignments
     */
    private function assignMassAssignmentSortByUser(array $massAssignments)
    {
        foreach ($massAssignments as $assignment) {
            $this->massAssignmentSortByUser[$assignment->getUser()->getId()][] = $assignment;
        }
    }

    public function isUsable(Sheet $sheet, MeetingSlot $slot): bool
    {
        $this->autoLoading($slot->getEvent());

        return !$this->hasMassUnavailabilityOnSameSlot($sheet, $slot);
    }

    /**
     * @param MeetingSlot $slot
     * @param Participant $participant
     *
     * @return SlotAvailabilityView
     */
    public function getSlotAvailability(MeetingSlot $slot, Participant $participant)
    {
        $this->autoLoading($participant->getSheet()->getEvent());

        if (false !== ($meeting = $this->hasMeeting($slot, $participant))) {
            return new SlotAvailabilityView(self::MEETING_UNAVAILABILITY, $meeting);
        }

        if (null !== ($otherSheet = $this->getMeetingOnOtherSheet($slot, $participant))) {
            return new SlotAvailabilityView(self::MEETING_ON_OTHER_SHEET, null, null, $otherSheet);
        }

        if ($this->hasUnavailability($slot, $participant)) {
            return new SlotAvailabilityView(self::UNAVAILABILITY);
        }

        if ($this->hasHappening($slot, $participant)) {
            return new SlotAvailabilityView(self::HAPPENING_UNAVAILABILITY);
        }

        if (false !== ($assignment = $this->getMassUnavailability($slot, $participant))) {
            // result can be true or MassAssignment, if true, change it to null to send it to the object
            if (!$assignment instanceof MassAssignment) {
                $assignment = null;
            }

            return new SlotAvailabilityView(self::MASS_UNAVAILABILITY, null, $assignment);
        }

        return new SlotAvailabilityView(self::SLOT_AVAILABLE);
    }

    /**
     * @param MeetingSlot $slot
     * @param Participant $participant
     *
     * @return SlotAvailabilityView
     *
     * @deprecated use getSlotAvailability()
     */
    public function isAvailable(MeetingSlot $slot, Participant $participant)
    {
        return $this->getSlotAvailability($slot, $participant);
    }

    /**
     * Autoload if preload was not used
     *
     * @param Event $event
     */
    public function autoLoading(Event $event)
    {
        if (null === $this->happenings) {
            $this->happenings = $this->happeningParticipationRepository->getByEvent($event);

            $this->assignHappeningSortByParticipant($this->happenings);
        }

        if (null === $this->meetings) {
            $this->meetings = $this->meetingRepositoryInterface->getAllByEvent($event);

            $this->assignMeetingSortByParticipant($this->meetings);
        }

        if (null === $this->unavailability) {
            $this->unavailability = $this->unavailabilityRepository->getByEvent($event);

            $this->assignUnavailabilitySortByUser($this->unavailability);
        }

        if (null === $this->massUnavailability) {
            $this->massUnavailability = $this->massUnavailabilityRepository->findBlockingByEvent($event);
        }

        if (null === $this->massAssignment) {
            $this->massAssignment = $this->massAssignmentRepository->findByEvent($event);

            $this->assignMassAssignmentSortByUser($this->massAssignment);
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
        if (!isset($this->unavailabilitySortByUser[$participant->getUser()->getId()])) {
            return false;
        }

        /** @var Unavailability $unavailability */
        foreach ($this->unavailabilitySortByUser[$participant->getUser()->getId()] as $unavailability) {
            if (TimeOverlap::overlap($unavailability, $slot)) {
                return true;
            }
        }

        return false;
    }

    private function hasMassUnavailabilityOnSameSlot(Sheet $sheet, MeetingSlot $slot): bool
    {
        foreach ($this->massUnavailability as $mass) {
            if (!$mass->hasType($sheet->getType())) {
                continue;
            }

            if (TimeOverlap::overlap($slot, $mass)) {
                return $mass->isBlockingAndNotDispatch();
            }
        }

        return false;
    }

    private function getDispatch(Participant $participant, Mass $mass): ?MassAssignment
    {
        if (null !== $this->massAssignment) {
            if (!isset($this->massAssignmentSortByUser[$participant->getUser()->getId()])) {
                return null;
            }

            /** @var MassAssignment $massAssignment */
            foreach ($this->massAssignmentSortByUser[$participant->getUser()->getId()] as $massAssignment) {
                if ($massAssignment->getMass() === $mass
                    && $massAssignment->getUser()->getId() === $participant->getUser()->getId()
                ) {
                    return $massAssignment;
                }
            }
        }

        return null;
    }

    /**
     * @return bool|MassAssignment
     */
    private function getMassUnavailability(MeetingSlot $slot, Participant $participant)
    {
        foreach ($this->massUnavailability as $mass) {
            if (!$mass->hasType($participant->getSheet()->getType())) {
                continue;
            }

            if ($mass->isDispatch()) {
                $assignment = $this->getDispatch($participant, $mass);

                if (null !== $assignment) {
                    $assignmentResult = $this->getDispatchUnavailability($assignment, $slot);

                    if (self::ASSIGNMENT_DISABLED === $assignmentResult) {
                        return false;
                    }

                    if (self::ASSIGNMENT_FOUND === $assignmentResult) {
                        return $assignment;
                    }

                    continue;
                }
            }

            if (TimeOverlap::overlap($slot, $mass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string self::ASSIGNMENT_NOT_FOUND|self::ASSIGNMENT_FOUND|self::ASSIGNMENT_DISABLED
     */
    private function getDispatchUnavailability(MassAssignment $massAssignment, MeetingSlot $slot): string
    {
        if (TimeOverlap::overlap($slot, $massAssignment)) {
            return $massAssignment->isEnabled() ? self::ASSIGNMENT_FOUND : self::ASSIGNMENT_DISABLED;
        }

        return self::ASSIGNMENT_NOT_FOUND;
    }

    /**
     * @param MeetingSlot $slot
     * @param Participant $participant
     *
     * @return Meeting|false
     */
    private function hasMeeting(MeetingSlot $slot, Participant $participant)
    {
        if (!isset($this->meetingsSortByParticipant[$participant->getId()])) {
            return false;
        }

        foreach ($this->meetingsSortByParticipant[$participant->getId()] as $meeting) {
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
     * @return null|Sheet
     */
    private function getMeetingOnOtherSheet(MeetingSlot $slot, Participant $participant)
    {
        if (empty($this->meetingOtherSheets)) {
            return null;
        }

        foreach ($this->meetingOtherSheets as $meetingOtherSheet) {
            if ($meetingOtherSheet->getSlot()->getId() === $slot->getId()) {
                $otherSheet = $meetingOtherSheet->getSheetByUser($participant->getUser());

                if (null !== $otherSheet) {
                    return $otherSheet;
                }
            }
        }

        return null;
    }

    private function hasHappening(MeetingSlot $slot, Participant $participant): bool
    {
        if (!isset($this->happeningsSortByParticipant[$participant->getUser()->getId()])) {
            return false;
        }

        /** @var HappeningParticipation $happeningParticipation */
        foreach ($this->happeningsSortByParticipant[$participant->getUser()->getId()] as $happeningParticipation) {
            if ($happeningParticipation->getUser() !== $participant->getUser()) {
                continue;
            }

            if (TimeOverlap::overlap($slot, $happeningParticipation->getHappening())) {
                return true;
            }
        }

        return false;
    }
}
