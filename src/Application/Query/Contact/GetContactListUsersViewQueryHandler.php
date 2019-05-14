<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class GetContactListUsersViewQueryHandler
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingParticipants */
    private $meetingParticipants;

    /** @var ContactRepositoryInterface */
    private $contactRepository;

    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MeetingParticipants $meetingParticipants,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->requestRepository = $requestRepository;
        $this->meetingParticipants = $meetingParticipants;
        $this->contactRepository = $contactRepository;
    }

    public function handle(GetContactListUsersViewQuery $query): GetContactListUsersView
    {
        $requestedUsers = $this->getFromApprovedRequests($query->participant);
        $scannedUsers = $this->getFromContacts($query->event, $query->participant->getUser());

        return new GetContactListUsersView($scannedUsers, $requestedUsers);
    }

    /**
     * @param Participant $participant
     *
     * @return User[]
     */
    protected function getFromApprovedRequests(Participant $participant): array
    {
        $sheet = $participant->getSheet();

        $requests = $this->requestRepository->findApproved($sheet);

        $metUsers = [];
        foreach ($requests as $request) {
            $requestParticipants = $this->meetingParticipants->getMeetingParticipants($request, $sheet);

            if (!\in_array($participant, $requestParticipants, true)) {
                continue;
            }

            foreach ($request->getSheetMet($sheet)->getParticipantsArray() as $otherParticipant) {
                $metUsers[] = $otherParticipant->getUser();
            }
        }

        return $metUsers;
    }

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return User[]
     */
    private function getFromContacts(Event $event, User $user): array
    {
        return $this->contactRepository->findSeenUserByEventAndUser($event, $user);
    }
}
