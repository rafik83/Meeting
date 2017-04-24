<?php

namespace Proximum\Vimeet\Application\View\Sheet\Group\Participant;

class ParticipantView
{
    /** @var string $firstName */
    public $firstName;

    /** @var string $lastName */
    public $lastName;

    /** @var string $fullName */
    public $fullName;

    /**
     * ParticipantView constructor.
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $fullName
     */
    public function __construct($firstName, $lastName, $fullName)
    {
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->fullName  = $fullName;
    }
}
