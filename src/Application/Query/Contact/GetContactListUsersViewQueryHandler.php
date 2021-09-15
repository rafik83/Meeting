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
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GetContactListUsersViewQueryHandler
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingParticipants */
    private $meetingParticipants;

    /** @var ContactRepositoryInterface */
    private $contactRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        RequestRepositoryInterface $requestRepository,
        MeetingParticipants $meetingParticipants,
        ContactRepositoryInterface $contactRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->requestRepository = $requestRepository;
        $this->meetingParticipants = $meetingParticipants;
        $this->contactRepository = $contactRepository;
        $this->sheetRepository = $sheetRepository;
        $this->meetingRepository = $meetingRepository;
    }

    public function handle(GetContactListUsersViewQuery $query): GetContactListUsersView
    {
        $user = $query->participant->getUser();
        $event = $query->event;
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        $metUsersInMeeting = $this->getMetUsersInMeeting($event, $sheets);
        $requestedUsers = $this->getFromApprovedRequests($sheets, $user);
        $scannedUsers = $this->getFromContacts($event, $user);

        return new GetContactListUsersView($scannedUsers, $requestedUsers, $metUsersInMeeting);
    }

    private function getMetUsersInMeeting(Event $event, array $sheets): array
    {
        $meetings = $this->meetingRepository->getBySheets($event, $sheets);
        $users = [];

        foreach ($meetings as $meeting) {
            $participantsMet = [];
            foreach ($sheets as $sheet) {
                $participantsMet = $meeting->getMetParticipants($sheet);

                if (!empty($participantsMet)) {
                    break 1;
                }
            }

            foreach ($participantsMet as $participantMet) {
                $users[] = $participantMet->getUser();
            }
        }

        return $users;
    }

    /**
     * @param Sheet[] $sheets
     * @param User    $user
     *
     * @return User[]
     */
    protected function getFromApprovedRequests(array $sheets, User $user): array
    {
        $metUsers = [];

        foreach ($sheets as $sheet) {
            $participantOfSheet = $sheet->getUserParticipant($user);

            // Avoid a request if the user is not participating on the sheet.
            if (null === $participantOfSheet) {
                continue;
            }

            $requests = $this->requestRepository->findApproved($sheet);

            foreach ($requests as $request) {
                if (!$this->isRequestNoPreferenceOrInParticipants($request, $participantOfSheet)) {
                    continue;
                }

                foreach ($this->getSheetParticipantsMet($request, $sheet) as $participantMet) {
                    $metUsers[] = $participantMet->getUser();
                }
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
