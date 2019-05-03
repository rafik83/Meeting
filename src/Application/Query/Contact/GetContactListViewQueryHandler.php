<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetContactListViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingParticipants */
    private $meetingParticipants;

    /** @var ContactRepositoryInterface */
    private $contactRepository;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        RequestRepositoryInterface $requestRepository,
        MeetingParticipants $meetingParticipants,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->requestRepository = $requestRepository;
        $this->meetingParticipants = $meetingParticipants;
        $this->contactRepository = $contactRepository;
    }

    /**
     * @param GetContactListViewQuery $query
     *
     * @return ContactPreviewView[]
     */
    public function handle(GetContactListViewQuery $query): array
    {
        // get users
        $requestedUsers = $this->getFromApprovedRequests($query->participant);
        $scannedUsers = $this->getFromContacts($query->event, $query->participant->getUser());

        /** @var User[] $metUsers */
        $metUsers = array_merge($requestedUsers, $scannedUsers);

        // convert users to view
        $contactListView = [];

        $parsedUserIds = [];
        foreach ($metUsers as $contact) {
            if (isset($parsedUserIds[$contact->getId()])) {
                continue;
            }
            $parsedUserIds[$contact->getId()] = 1;

            $sheetsOfContact = $this->sheetRepository->getSheetsByUserAndEvent($contact, $query->event);
            $participantOfContact = $this->getParticipant($sheetsOfContact, $contact);

            $contactSheetViews = [];
            foreach ($sheetsOfContact as $sheetOfContact) {
                $contactSheetViews[] = $sheetOfContact->getTitle();
            }

            usort(
                $contactSheetViews,
                static function ($titleA, $titleB) {
                    return mb_strtolower($titleA) <=> mb_strtolower($titleB);
                }
            );

            $infos = $this->participantInfoGuesser->guessParticipantInfos($participantOfContact, $query->locale);
            $contactListView[] = new ContactPreviewView(
                $contact->getId(),
                $infos[Tag::PARTICIPANT_FIRSTNAME],
                $infos[Tag::PARTICIPANT_LASTNAME],
                $infos[Tag::PARTICIPANT_AVATAR],
                $contactSheetViews,
                \in_array($contact, $requestedUsers, true)
            );
        }

        usort(
            $contactListView,
            static function (ContactPreviewView $contactA, ContactPreviewView $contactB) {
                $compare = mb_strtolower(implode($contactA->sheetTitles))
                    <=> mb_strtolower(implode($contactB->sheetTitles));

                if (0 === $compare) {
                    $compare = mb_strtolower($contactA->firstName.' '.$contactA->lastName)
                        <=> mb_strtolower($contactB->firstName.' '.$contactB->lastName);
                }

                return $compare;
            }
        );

        return $contactListView;
    }

    /**
     * @param Sheet[] $sheets
     * @param User    $contact
     *
     * @return Participant|null
     */
    private function getParticipant(array $sheets, User $contact): ?Participant
    {
        foreach ($sheets as $sheet) {
            $participant = $sheet->getUserParticipant($contact);
            if (null !== $participant) {
                return $participant;
            }
        }

        return null;
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
        return $this->contactRepository->findByEventAndUser($event, $user);
    }
}
