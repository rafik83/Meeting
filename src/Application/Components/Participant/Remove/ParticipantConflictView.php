<?php

namespace Proximum\Vimeet\Application\Components\Participant\Remove;

class ParticipantConflictView
{
    /** @var int */
    public $participantId;

    /** @var string */
    public $participantName;

    public function __construct(
        int $participantId,
        $participantName
    ) {
        $this->participantId = $participantId;
        $this->participantName = $participantName;
    }
}
