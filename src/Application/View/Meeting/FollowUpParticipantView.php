<?php

namespace Proximum\Vimeet\Application\View\Meeting;

use Proximum\Vimeet\Domain\Participant\GetParticipantInitials;

class FollowUpParticipantView
{
    public string $firstname;
    public string $lastname;
    public string $position;
    public int $sheetId;
    public ?string $avatar;
    public ?string $email;
    public ?string $phone;
    public string $initials;

    public function __construct(
        string $firstname,
        string $lastname,
        string $position,
        string $sheetId,
        ?string $avatar,
        ?string $email,
        ?string $phone
    ) {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->position = $position;
        $this->sheetId = $sheetId;
        $this->avatar = $avatar;
        $this->email = $email;
        $this->phone = $phone;

        $this->initials = (new GetParticipantInitials())($firstname, $lastname);
    }
}
