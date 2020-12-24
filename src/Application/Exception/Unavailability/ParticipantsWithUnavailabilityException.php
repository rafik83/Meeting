<?php

namespace Proximum\Vimeet\Application\Exception\Unavailability;

class ParticipantsWithUnavailabilityException extends UnavailabilityException
{
    /** @var array of Participants names */
    public $participantNames;

    /**
     * @param array $participantNames
     */
    public function __construct(array $participantNames = [])
    {
        parent::__construct('Selected participants have already an unavailability');

        $this->participantNames = $participantNames;
    }

    public function getListOfParticipantsName(): string
    {
        return implode(', ', $this->participantNames);
    }
}
