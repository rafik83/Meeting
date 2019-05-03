<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Contact\CanParticipantSeeContact;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Exception\Sheet\AccessDeniedException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetContactViewQueryHandler
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var RouterInterface */
    private $router;

    /** @var CanParticipantSeeContact */
    private $canParticipantSeeContact;

    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        RouterInterface $router,
        SheetRepositoryInterface $sheetRepository,
        CanParticipantSeeContact $canParticipantSeeContact
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->router = $router;
        $this->sheetRepository = $sheetRepository;
        $this->canParticipantSeeContact = $canParticipantSeeContact;
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

        if (!$this->canParticipantSeeContact->isSatisfiedBy($query->seerParticipant, $contact)) {
            throw new AccessDeniedException();
        }

        $contactSheetViews = [];

        foreach ($sheetsOfContact as $sheetOfContact) {
            $contactSheetViews[] = new ContactSheetView(
                $sheetOfContact->getTitle(),
                $this->router->generate(
                    'event_catalog_complete_sheet',
                    [
                        'sheet' => $query->userSheet->getId(), 'sheetToDisplay' => $sheetOfContact->getId(),
                    ]
                )
            );
        }

        $infos = $this->participantInfoGuesser->guessParticipantInfos($participantOfContact, $query->locale);

        return new ContactView(
            $infos[Tag::PARTICIPANT_FIRSTNAME],
            $infos[Tag::PARTICIPANT_LASTNAME],
            $infos[Tag::PARTICIPANT_POSITION],
            $infos[Tag::PARTICIPANT_AVATAR],
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
