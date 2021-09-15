<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetMeViewQueryHandler
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        RouterInterface $router,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->router = $router;
        $this->sheetRepository = $sheetRepository;
    }

    public function handle(GetMeViewQuery $query): ContactView
    {
        $user = $query->participant->getUser();
        $sheet = $query->participant->getSheet();
        $sheetsOfContact = $this->sheetRepository->getSheetsByUserAndEvent($user, $query->event);
        $participantOfContact = $this->getParticipant($sheetsOfContact, $user);

        if (null === $participantOfContact) {
            throw new ContactParticipantNotFoundException();
        }

        $contactSheetViews = [];

        foreach ($sheetsOfContact as $sheetOfContact) {
            $contactSheetViews[] = new ContactSheetView(
                $sheetOfContact->getTitle(),
                $this->router->generate(
                    'event_catalog_complete_sheet',
                    [
                        'sheet' => $sheet->getId(), 'sheetToDisplay' => $sheetOfContact->getId(),
                    ]
                )
            );
        }

        $infos = $this->participantInfoGuesser->guessParticipantInfos($participantOfContact, $query->locale);

        return new ContactView(
            $user->getId(),
            $infos[Tag::PARTICIPANT_FIRSTNAME],
            $infos[Tag::PARTICIPANT_LASTNAME],
            $infos[Tag::PARTICIPANT_POSITION],
            $infos[Tag::PARTICIPANT_AVATAR],
            null,
            null,
            $contactSheetViews
        );
    }

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
