<?php

namespace Proximum\Vimeet\Application\Query\Participant;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\CachedNetworkingStatusInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class CardViewQueryHandler
{
    private ParticipantInfoGuesser $participantInfoGuesser;
    private ScanRepositoryInterface $scanRepository;
    private DateTimeInterface $dateTime;

    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        ScanRepositoryInterface $scanRepository,
        CachedNetworkingStatusInterface $cachedNetworkingStatus,
        DateTimeInterface $dateTime
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->scanRepository = $scanRepository;
        $this->cachedNetworkingStatus = $cachedNetworkingStatus;
        $this->dateTime = $dateTime;
    }

    public function handle(CardViewQuery $cardViewQuery): CardView
    {
        $infos = $this->participantInfoGuesser->guessParticipantInfos($cardViewQuery->participant, $cardViewQuery->locale);
        $isOnline = null;

        if($cardViewQuery->showMeetOnline){
            $isOnline = $this->cachedNetworkingStatus->isOnline(
                $cardViewQuery->participant->getSheet()->getEvent()->getId(),
                $cardViewQuery->participant->getUser()->getId()
            );
        }

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
            $cardViewQuery->getCheckinStatus && $this->scanRepository->isUserCheckinTodayByEvent(
                $cardViewQuery->participant->getUser(),
                $cardViewQuery->participant->getSheet()->getEvent(),
                $this->dateTime
            ),
            $isOnline,
            $cardViewQuery->participant->getUser()->getId()
        );
    }
}
