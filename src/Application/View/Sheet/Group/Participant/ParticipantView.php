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

    /** @var array */
    public $dayViews;

    /**
     * ParticipantView constructor.
     *
     * @param string $firstName
     * @param string $lastName
     * @param array  $dayViews
     */
    public function __construct($firstName, $lastName, array $dayViews)
    {
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->fullName  = $firstName . ' ' . $lastName;
        $this->dayViews  = $dayViews;
    }
}
