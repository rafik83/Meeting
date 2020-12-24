<?php

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class UnavailabilityBatch
{
    /**
     * @var Event
     */
    private $event;

    /**
     * Array of Spot ids
     *
     * @var array
     */
    private $spotIds;

    /**
     * @var MeetingSlot[]
     */
    public $meetingSlots;

    /**
     * UnavailabilityBatch constructor.
     *
     * @param array $ids          "Array of Spot ids"
     * @param Event $event
     * @param array $meetingSlots
     */
    public function __construct(array $ids, Event $event, array $meetingSlots = [])
    {
        $this->spotIds      = $ids;
        $this->event        = $event;
        $this->meetingSlots = $meetingSlots;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Array of Spot ids
     *
     * @return array
     */
    public function getSpotIds()
    {
        return $this->spotIds;
    }
}
