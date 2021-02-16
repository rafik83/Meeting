<?php

namespace Proximum\Vimeet\Domain\View;

class ParticipantNameView
{
    /**
     * @var string
     */
    public $participantName;

    /**
     * @param string $participantName
     */
    public function __construct($participantName)
    {
        $this->participantName = $participantName;
    }
}
