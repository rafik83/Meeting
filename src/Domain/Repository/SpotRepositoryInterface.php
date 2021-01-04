<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
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
     * @param bool      $visio
     *
     * @return null|Spot
     */
    public function find(Event $event, $id, $visio = false);

    /**
     * @param Event $event
     * @param array $ids
     *
     * @return Spot[]
     */
    public function findMany(Event $event, array $ids);

    /**
     * @param Event $event
     *
     * @return Spot[]
     */
    public function getActiveByEvent(Event $event): array;

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function hasActiveSpot(Event $event): bool;

    /**
     * @param Event $event
     *
     * @return Spot[]
     */
    public function getAllByEvent(Event $event): array;

    /**
     * @param Event $event
     * @param array $filter
     *
     * @return Spot[]
     */
    public function getSpotFilter(Event $event, array $filter = []);

    /**
     * @param array $spotsIds
     *
     * @return Spot[]
     */
    public function getSpotsByIds(array $spotsIds = []);

    /**
     * @param Event  $event
     * @param string $reference
     *
     * @return Spot|null
     */
    public function findByReference(Event $event, $reference);

    /**
     * @param Spot[] $spots
     * @param Event  $event
     */
    public function removeBatchSpot(array $spots, Event $event);

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
     * @param bool    $visio
     *
     * @return Spot[]
     */
    public function getSpotsForMeeting(Meeting $meeting, $visio = false);

    /**
     * @param Spot $spot
     *
     * @return bool
     */
    public function hasMeeting(Spot $spot);

    /**
     * @param Meeting $meeting
     * @param bool    $visio
     *
     * @return bool
     */
    public function hasSpotsForMeeting(Meeting $meeting, $visio = false);

    /**
     * @param MeetingSlot  $slot
     * @param int          $participantsQuantity
     * @param Meeting|null $exceptMeeting
     * @param Sheet|null   $fromSheet
     * @param Sheet|null   $toSheet
     * @param bool         $visio
     *
     * @return \Proximum\Vimeet\Domain\Model\Spot[]
     */
    public function getSpotsForSlotAndParticipantsQuantity(
        MeetingSlot $slot,
        $participantsQuantity,
        Meeting $exceptMeeting = null,
        Sheet $fromSheet = null,
        Sheet $toSheet = null,
        $visio = false
    );

    /**
     * @param MeetingSlot  $slot
     * @param int          $participantsQuantity
     * @param Meeting|null $exceptMeeting
     * @param Sheet|null   $fromSheet
     * @param Sheet|null   $toSheet
     * @param bool         $visio
     *
     * @return bool
     */
    public function hasSpotsForSlotAndParticipantsQuantity(
        MeetingSlot $slot,
        $participantsQuantity,
        Meeting $exceptMeeting = null,
        Sheet $fromSheet = null,
        Sheet $toSheet = null,
        $visio = false
    );

    /**
     * @param Event $event
     *
     * @return Spot[]
     */
    public function findSharedByEvent(Event $event): array;
}
