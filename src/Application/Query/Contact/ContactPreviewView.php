<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Domain\Participant\GetParticipantInitials;

class ContactPreviewView
{
    /** @var int */
    public $contactId;

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

    /** @var bool */
    public $needEvaluation;

    /** @var bool */
    public $isCheckedToday;

    public function __construct(
        int $contactId,
        string $firstName,
        string $lastName,
        string $avatar,
        array $sheetTitles,
        bool $hasApprovedMeetingRequestWith,
        bool $needEvaluation,
        bool $isCheckedToday
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->initials = (new GetParticipantInitials())($firstName, $lastName);
        $this->avatar = $avatar;
        $this->sheetTitles = $sheetTitles;
        $this->hasApprovedMeetingRequestWith = $hasApprovedMeetingRequestWith;
        $this->contactId = $contactId;
        $this->needEvaluation = $needEvaluation;
        $this->isCheckedToday = $isCheckedToday;
    }
}
