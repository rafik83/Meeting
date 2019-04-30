<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Domain\Participant\GetParticipantInitials;

class ContactView
{
    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string */
    public $initials;

    /** @var string */
    public $position;

    /** @var string */
    public $avatar;

    /** @var ContactSheetView[] */
    public $contactSheetViews;

    /**
     * @param string             $firstName
     * @param string             $lastName
     * @param string             $position
     * @param string             $avatar
     * @param ContactSheetView[] $contactSheetViews
     */
    public function __construct(
        string $firstName,
        string $lastName,
        string $position,
        string $avatar,
        array $contactSheetViews
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->initials = (new GetParticipantInitials())($firstName, $lastName);
        $this->position = $position;
        $this->avatar = $avatar;
        $this->contactSheetViews = $contactSheetViews;
    }
}
