<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Meeting;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\ParticipantExtraData;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\ParticipantExtraDataRepositoryInterface;

class ParticipantExtraDataRepository implements ParticipantExtraDataRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(ParticipantExtraData $participantExtraData): void
    {
        $this->entityManager->persist($participantExtraData);
        $this->entityManager->flush($participantExtraData);
    }

    public function set(ParticipantExtraData $participantExtraData): void
    {
        $this->entityManager->flush($participantExtraData);
    }

    public function findOneByParticipantAndMeetingAndType(Participant $participant, Meeting $meeting, string $type): ?ParticipantExtraData
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('participantExtraData')
            ->from(ParticipantExtraData::class, 'participantExtraData')
            ->where('participantExtraData.participant = :participant')
            ->andWhere('participantExtraData.meeting = :meeting')
            ->andWhere('participantExtraData.type = :type')
            ->setParameters([
                'participant' => $participant->getId(),
                'meeting' => $meeting->getId(),
                'type' => $type,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
