<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;

interface MeetingSlotRepositoryInterface
{
    /**
     * @param MeetingSlot $meetingSlot
     */
    public function add(MeetingSlot $meetingSlot);

    /**
     * @param MeetingSlot $meetingSlot
     */
    public function set(MeetingSlot $meetingSlot);

    /**
     * @param MeetingSlot $meetingSlot
     */
    public function remove(MeetingSlot $meetingSlot);

    /**
     * @param Event $event
     * @param int   $slotId
     *
     * @return null|MeetingSlot
     */
    public function find(Event $event, $slotId);

    /**
     * @param Event $event
     *
     * @return MeetingSlot[]
     */
    public function findByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return MeetingSlot[]
     */
    public function getAvailableSlotByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countByEvent(Event $event);

    /**
     * @param Event         $event
     * @param Participant[] $participants
     * @param bool          $ignoreMeetings
     * @param Meeting       $exceptedMeeting
     *
     * @return MeetingSlot[]
     */
    public function findAvailableSlotsByParticipants(
        Event $event,
        array $participants,
        $ignoreMeetings = false,
        Meeting $exceptedMeeting = null
    );

    /**
     * @param Event $event
     * @param Day   $day
     *
     * @return MeetingSlot[]
     */
    public function findByEventAndDay(Event $event, Day $day);

    /**
     * @param Event $event
     *
     * @return array
     */
    public function findWithAtLeastOneMeetingByEvent(Event $event);

    /**
     * @param int $slotId
     *
     * @return null|MeetingSlot
     */
    public function findById($slotId): ?MeetingSlot;

    /**
     * @param array $slotIds
     *
     * @return MeetingSlot[]
     */
    public function findByIds(array $slotIds): array;

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function hasActiveSlot(Event $event): bool;

    /**
     * @param Event[] $events
     *
     * @return int[]
     */
    public function findSlotIdsByEvents(array $events): array;
}
