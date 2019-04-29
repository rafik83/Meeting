<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetContactListViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingParticipants */
    private $meetingParticipants;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        RequestRepositoryInterface $requestRepository,
        MeetingParticipants $meetingParticipants
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->requestRepository = $requestRepository;
        $this->meetingParticipants = $meetingParticipants;
    }

    /**
     * @param GetContactListViewQuery $query
     *
     * @return ContactPreviewView[]
     */
    public function handle(GetContactListViewQuery $query): array
    {
        $sheet = $query->participant->getSheet();
        $requests = $this->requestRepository->findApproved($sheet);

        $metParticipants = [[]];
        foreach ($requests as $request) {
            $requestParticipants = $this->meetingParticipants->getMeetingParticipants($request, $sheet);

            if (\in_array($query->participant, $requestParticipants, true)) {
                $metParticipants[] = $request->getSheetMet($sheet)->getParticipantsArray();
            }
        }

        $metParticipants = array_merge(...$metParticipants);
        $users = array_map(
            static function (Participant $participant) {
                return $participant->getUser();
            },
            $metParticipants
        );

        $parsedUserIds = [];
        $users = array_filter(
            $users,
            static function (User $user) use (&$parsedUserIds) {
                if (isset($parsedUserIds[$user->getId()])) {
                    return false;
                }

                $parsedUserIds[$user->getId()] = 1;

                return true;
            }
        );

        $contactListView = [];

        foreach ($users as $contact) {
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
                $infos[Tag::PARTICIPANT_FIRSTNAME],
                $infos[Tag::PARTICIPANT_LASTNAME],
                $infos[Tag::PARTICIPANT_AVATAR],
                $contactSheetViews,
                true
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
}
