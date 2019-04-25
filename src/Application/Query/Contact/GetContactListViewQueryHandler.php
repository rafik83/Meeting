<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetContactListViewQueryHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param GetContactListViewQuery $query
     *
     * @return ContactPreviewView[]
     */
    public function handle(GetContactListViewQuery $query): array
    {
        $users = $this->userRepository->getMet($query->event, $query->user);

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
                $contactSheetViews
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
