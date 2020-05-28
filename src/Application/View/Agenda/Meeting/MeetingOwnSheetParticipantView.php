<?php

namespace Proximum\Vimeet\Application\View\Agenda\Meeting;

class MeetingOwnSheetParticipantView
{
    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string|null */
    public $position;

    public function __toString()
    {
        return $this->getFullName();
    }

    public function __construct(
        string $firstName,
        string $lastName,
        ?string $position
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->position = $position;
    }

    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
