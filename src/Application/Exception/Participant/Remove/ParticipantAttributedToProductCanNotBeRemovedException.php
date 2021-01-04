<?php

namespace Proximum\Vimeet\Application\Exception\Participant\Remove;

class ParticipantAttributedToProductCanNotBeRemovedException extends RemoveException
{
    /** @var array of participant name */
    private $participantNames;

    public function __construct(array $participantNames)
    {
        parent::__construct();

        $this->participantNames = $participantNames;
    }

    public function countParticipants(): int
    {
        return \count($this->participantNames);
    }

    public function getParticipantNames(): string
    {
        return implode(', ', $this->participantNames);
    }
}
