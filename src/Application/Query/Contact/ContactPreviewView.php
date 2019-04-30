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
    public $hasApprovedMeetingRequestWith;

    /**
     * @param string   $firstName
     * @param string   $lastName
     * @param string   $avatar
     * @param string[] $sheetTitles
     * @param bool     $hasApprovedMeetingRequestWith
     */
    public function __construct(
        string $firstName,
        string $lastName,
        string $avatar,
        array $sheetTitles,
        bool $hasApprovedMeetingRequestWith
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->initials = (new GetParticipantInitials())($firstName, $lastName);
        $this->avatar = $avatar;
        $this->sheetTitles = $sheetTitles;
        $this->hasApprovedMeetingRequestWith = $hasApprovedMeetingRequestWith;
    }
}
