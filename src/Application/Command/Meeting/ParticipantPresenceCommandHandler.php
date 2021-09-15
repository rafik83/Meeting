<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\ParticipantExtraData;
use Proximum\Vimeet\Domain\Repository\Meeting\ParticipantExtraDataRepositoryInterface;

class ParticipantPresenceCommandHandler
{
    /** @var ParticipantExtraDataRepositoryInterface */
    private $participantExtraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ParticipantExtraDataRepositoryInterface $participantExtraDataRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->participantExtraDataRepository = $participantExtraDataRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(ParticipantPresenceCommand $command): void
    {
        $participantExtraData = $this->participantExtraDataRepository->findOneByParticipantAndMeetingAndType(
            $command->participant,
            $command->meeting,
            ParticipantExtraData::TYPE_PRESENCE
        );

        if (!$participantExtraData instanceof ParticipantExtraData) {
            $this->participantExtraDataRepository->add(
                new ParticipantExtraData(
                    ParticipantExtraData::TYPE_PRESENCE,
                    $command->participant,
                    $command->meeting,
                    $this->dateTime
                )
            );

            return;
        }

        $participantExtraData->setDate($this->dateTime);
        $this->participantExtraDataRepository->set($participantExtraData);
    }
}
