<?php

namespace Proximum\Vimeet\Application\Components\Contact;

use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersViewQuery;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;

class CanParticipantSeeContact
{
    /** @var GetContactListUsersViewQueryHandler */
    private $getContactListUsersViewQueryHandler;

    public function __construct(GetContactListUsersViewQueryHandler $getContactListUsersViewQueryHandler)
    {
        $this->getContactListUsersViewQueryHandler = $getContactListUsersViewQueryHandler;
    }

    public function isSatisfiedBy(Participant $seerParticipant, User $seenUser): bool
    {
        $usersView = $this->getContactListUsersViewQueryHandler->handle(
            new GetContactListUsersViewQuery($seerParticipant->getEvent(), $seerParticipant)
        );

        $metUsers = array_merge(
            $usersView->inContactsUsers,
            $usersView->requestsUsers,
            $usersView->metInMeetingUsers
        );

        return \in_array($seenUser, $metUsers, true);
    }
}
