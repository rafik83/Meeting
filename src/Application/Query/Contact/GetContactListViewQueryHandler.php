<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetContactListViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var GetContactListUsersViewQueryHandler */
    private $getContactListUsersViewQueryHandler;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        GetContactListUsersViewQueryHandler $getContactListUsersViewQueryHandler
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->getContactListUsersViewQueryHandler = $getContactListUsersViewQueryHandler;
    }

    /**
     * @param GetContactListViewQuery $query
     *
     * @return ContactPreviewView[]
     */
    public function handle(GetContactListViewQuery $query): array
    {
        $usersView = $this->getContactListUsersViewQueryHandler->handle(
            new GetContactListUsersViewQuery($query->event, $query->participant)
        );

        /** @var User[] $metUsers */
        $metUsers = array_merge($usersView->requestsUsers, $usersView->inContactsUsers);

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

            if (null === $participantOfContact) {
                continue;
            }

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
                \in_array($contact, $usersView->requestsUsers, true),
                !\in_array($contact, $usersView->inContactsUsers, true)
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
