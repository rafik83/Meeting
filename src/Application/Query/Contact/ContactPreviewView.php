<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Domain\Participant\GetParticipantInitials;

class ContactPreviewView
{
    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string */
    public $initials;

    /** @var string */
    public $avatar;

    /** @var string[] */
    public $sheetTitles;

    /** @var bool */
    public $hasMeetingWith;

    /**
     * @param string   $firstName
     * @param string   $lastName
     * @param string   $avatar
     * @param string[] $sheetTitles
     * @param bool     $hasMeetingWith
     */
    public function __construct(
        string $firstName,
        string $lastName,
        string $avatar,
        array $sheetTitles,
        bool $hasMeetingWith
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->initials = (new GetParticipantInitials())($firstName, $lastName);
        $this->avatar = $avatar;
        $this->sheetTitles = $sheetTitles;
        $this->hasMeetingWith = $hasMeetingWith;
    }
}
