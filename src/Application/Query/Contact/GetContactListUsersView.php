<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Domain\Model\User;

class GetContactListUsersView
{
    /** @var User[] */
    public $inContactsUsers;

    /** @var User[] */
    public $requestsUsers;

    /** @var User[] */
    public $metInMeetingUsers;

    public function __construct(
        array $scannedUsers,
        array $requestsUsers,
        array $metInMeetingUsers = []
    ) {
        $this->inContactsUsers = $scannedUsers;
        $this->requestsUsers = $requestsUsers;
        $this->metInMeetingUsers = $metInMeetingUsers;
    }
}
