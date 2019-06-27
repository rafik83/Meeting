<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
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
        $metUsers = [];
        $sheet = $participant->getSheet();
        $requests = $this->requestRepository->findApproved($sheet);

        foreach ($requests as $request) {
            if (!$this->isRequestNoPreferenceOrInParticipants($request, $participant)) {
                continue;
            }

            foreach ($this->getSheetParticipantsMet($request, $sheet) as $participantMet) {
                $metUsers[] = $participantMet->getUser();
            }
        }

        return $metUsers;
    }

    /**
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return Participant[]
     */
    private function getSheetParticipantsMet(Request $request, Sheet $sheet): array
    {
        $sheetMet = $request->getSheetMet($sheet);

        $participantsMet = $this->meetingParticipants->getMeetingParticipants($request, $sheetMet);

        if (empty($participantsMet)) {
            $participantsMet = [$sheetMet->getFirstParticipant()];
        }

        return $participantsMet;
    }

    private function isRequestNoPreferenceOrInParticipants(Request $request, Participant $participant): bool
    {
        $sheet = $participant->getSheet();

        if ($request->hasNoPreference($sheet)) {
            return true;
        }

        $requestParticipants = $this->meetingParticipants->getMeetingParticipants($request, $sheet);

        return \in_array($participant, $requestParticipants, true);
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
