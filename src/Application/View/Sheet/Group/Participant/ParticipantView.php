<?php

namespace Proximum\Vimeet\Application\View\Sheet\Group\Participant;

class ParticipantView
{
    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string */
    public $fullName;

    /** @var AgendaDayView[] */
    public $dayViews;

    /**
     * ParticipantView constructor.
     *
     * @param string          $firstName
     * @param string          $lastName
     * @param AgendaDayView[] $dayViews
     */
    public function __construct($firstName, $lastName, array $dayViews)
    {
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->fullName  = $firstName . ' ' . $lastName;
        $this->dayViews  = $dayViews;
    }
}
