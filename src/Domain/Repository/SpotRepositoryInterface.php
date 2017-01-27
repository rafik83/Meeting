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
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;

interface SpotRepositoryInterface
{
    /**
     * @param Spot $spot
     */
    public function add(Spot $spot);

    /**
     * @param Spot $spot
     */
    public function set(Spot $spot);

    /**
     * @param Event     $event
     * @param int|array $id
     *
     * @return Spot|Spot[]|null
     */
    public function find(Event $event, $id);

    /**
     * @param Event $event
     *
     * @return Spot[]
     */
    public function getActiveByEvent(Event $event);

    /**
     * @param Event $event
     * @param array $filter
     *
     * @return Spot[]
     */
    public function getSpotFilter(Event $event, array $filter = []);

    /**
     * @param Event  $event
     * @param string $reference
     *
     * @return Spot
     */
    public function findByReference(Event $event, $reference);

    /**
     * @param array $ids
     * @param Event $event
     */
    public function removeBatchSpot(array $ids, Event $event);

    /**
     * @param array $ids
     * @param Event $event
     */
    public function disableBatchSpot(array $ids, Event $event);

    /**
     * @param array $ids
     * @param Event $event
     */
    public function enableBatchSpot(array $ids, Event $event);

    /**
     * @param Meeting $meeting
     *
     * @return Spot[]
     */
    public function getSpotsForMeeting(Meeting $meeting);

    /**
     * @param MeetingSlot  $slot
     * @param int          $participantsQuantity
     * @param Meeting|null $exceptMeeting
     *
     * @return Spot[]
     */
    public function getSpotsForSlotAndParticipantsQuantity(
        MeetingSlot $slot,
        $participantsQuantity,
        Meeting $exceptMeeting = null
    );
}
