<?php

namespace Proximum\Vimeet\Application\Event\Meeting;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class MeetingRemovedEvent extends Event
{
    /** @var Sheet[] */
    private $sheets;

    /** @var Participant[] */
    private $participants;

    /**
     * @param Sheet[]       $sheets
     * @param Participant[] $participants
     */
    public function __construct(array $sheets, array $participants = [])
    {
        $this->sheets = $sheets;
        $this->participants = $participants;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets(): array
    {
        return $this->sheets;
    }

    /**
     * @return Participant[]
     */
    public function getParticipants(): array
    {
        return $this->participants;
    }
}
