<?php

namespace Proximum\Vimeet\Application\View\Agenda\Meeting;

class MeetingOwnSheetParticipantView
{
    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    public function __toString()
    {
        return $this->getFullName();
    }

    public function __construct(string $firstName, string $lastName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
