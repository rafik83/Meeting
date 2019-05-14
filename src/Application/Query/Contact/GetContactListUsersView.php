<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Domain\Model\User;

class GetContactListUsersView
{
    /** @var User[] */
    public $inContactsUsers;

    /** @var User[] */
    public $requestsUsers;

    public function __construct(array $scannedUsers, array $requestsUsers)
    {
        $this->inContactsUsers = $scannedUsers;
        $this->requestsUsers = $requestsUsers;
    }
}
