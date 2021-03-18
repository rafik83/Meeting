<?php

namespace Proximum\Vimeet\Domain\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Repository\Meeting\ParticipantExtraDataRepositoryInterface;

class IsParticipantPresentToMeeting
{
    /** @var ParticipantExtraDataRepositoryInterface */
    private $participantExtraDataRepository;

    /** @var \DateTimeInterface */
    private $date;

    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    public function __construct(
        ParticipantExtraDataRepositoryInterface $participantExtraDataRepository,
        \DateTimeInterface $dateTime,
        IsParticipantVisio $isParticipantVisio
    ) {
        $this->participantExtraDataRepository = $participantExtraDataRepository;
        $this->date = $dateTime;
        $this->isParticipantVisio = $isParticipantVisio;
    }

    public function isSatisfiedBy(Participant $participant, Meeting $meeting): bool
    {
        if (false === $this->isParticipantVisio->isSatisfiedBy($participant)) {
            return false;
        }

        $participantExtraData = $this->participantExtraDataRepository->findOneByParticipantAndMeetingAndType(
            $participant,
            $meeting,
            Meeting\ParticipantExtraData::TYPE_PRESENCE
        );

        if (!$participantExtraData instanceof Meeting\ParticipantExtraData) {
            return false;
        }

        $limitDate = (new \DateTime())
            ->setTimestamp($this->date->getTimestamp())
            ->modify('-3 minutes');

        return $participantExtraData->getDate() >= $limitDate;
    }
}
