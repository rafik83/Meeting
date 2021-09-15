<?php

namespace Proximum\Vimeet\Domain\Repository\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\ParticipantExtraData;
use Proximum\Vimeet\Domain\Model\Participant;

interface ParticipantExtraDataRepositoryInterface
{
    public function add(ParticipantExtraData $participantExtraData): void;
    public function set(ParticipantExtraData $participantExtraData): void;
    public function findOneByParticipantAndMeetingAndType(Participant $participant, Meeting $meeting, string $type): ?ParticipantExtraData;
}
