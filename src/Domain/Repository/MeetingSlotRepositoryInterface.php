<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

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
     * @param Event   $event
     * @param array   $participantsId
     * @param bool    $ignoreMeetings
     * @param Meeting $exceptedMeeting
     *
     * @return MeetingSlot[]
     */
    public function findAvailableSlotsByParticipantsIds(
        Event $event,
        array $participantsId,
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
}
