<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\ParticipantExtraData;
use Proximum\Vimeet\Domain\Repository\Meeting\ParticipantExtraDataRepositoryInterface;

class ParticipantPresenceCommandHandler
{
    /** @var ParticipantExtraDataRepositoryInterface */
    private $participantExtraDataRepository;

    /** @var \DateTimeInterface */
    private $datetime;

    public function __construct(
        ParticipantExtraDataRepositoryInterface $participantExtraDataRepository,
        \DateTimeInterface $datetime
    ) {
        $this->participantExtraDataRepository = $participantExtraDataRepository;
        $this->datetime = $datetime;
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
                    $this->datetime
                )
            );

            return;
        }

        $participantExtraData->setDate($this->datetime);
        $this->participantExtraDataRepository->set($participantExtraData);
    }
}
