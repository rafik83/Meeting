<?php

namespace Proximum\Vimeet\Application\Query\Participant;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class CardViewQueryHandler
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var ScanRepositoryInterface */
    private $scanRepository;

    /** @var \DateTimeInterface */
    private $now;

    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        ScanRepositoryInterface $scanRepository,
        \DateTimeInterface $now
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->scanRepository = $scanRepository;
        $this->now = $now;
    }

    public function handle(CardViewQuery $cardViewQuery): CardView
    {
        $infos = $this->participantInfoGuesser->guessParticipantInfos($cardViewQuery->participant, $cardViewQuery->locale);

        return new CardView(
            $cardViewQuery->participant->getId(),
            $cardViewQuery->editable,
            $infos[Tag::PARTICIPANT_FIRSTNAME],
            $infos[Tag::PARTICIPANT_LASTNAME],
            $infos[Tag::PARTICIPANT_POSITION],
            $infos[Tag::PARTICIPANT_AVATAR],
            $cardViewQuery->participant->isOwnerParticipant(),
            $cardViewQuery->participant->getSheet()->getId(),
            $cardViewQuery->getCheckinStatus,
            $cardViewQuery->getCheckinStatus
                ? $this->scanRepository->isUserCheckinTodayByEvent(
                    $cardViewQuery->participant->getUser(),
                    $cardViewQuery->participant->getSheet()->getEvent(),
                    $this->now
                )
                : false
        );
    }
}
