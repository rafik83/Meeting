<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetContactViewQueryHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(
        QueryBusInterface $queryBus,
        ParticipantInfoGuesser $participantInfoGuesser,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->queryBus = $queryBus;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param GetContactViewQuery $query
     *
     * @return ContactView
     */
    public function handle(GetContactViewQuery $query): ContactView
    {
        $contact = $query->contact;
        $sheetsOfContact = $this->sheetRepository->getSheetsByUserAndEvent($contact, $query->event);
        $participantOfContact = $this->getParticipant($sheetsOfContact, $contact);

        if (null === $participantOfContact) {
            throw new ContactParticipantNotFoundException();
        }

        $infos = $this->participantInfoGuesser->guessParticipantInfos($participantOfContact, $query->locale);

        return new ContactView(
            $infos[Tag::PARTICIPANT_FIRSTNAME],
            $infos[Tag::PARTICIPANT_LASTNAME],
            $infos[Tag::PARTICIPANT_POSITION],
            $infos[Tag::PARTICIPANT_AVATAR]
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
