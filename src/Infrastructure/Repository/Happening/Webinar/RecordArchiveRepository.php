<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Happening\Webinar;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;

class RecordArchiveRepository implements RecordArchiveRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(RecordArchive $recordArchive): void
    {
        $this->entityManager->persist($recordArchive);
        $this->entityManager->flush($recordArchive);
    }

    public function update(RecordArchive $recordArchive): void
    {
        $this->entityManager->flush($recordArchive);
    }

    public function getRecordArchivesForHappening(Happening $happening): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('recordArchive')
            ->from(RecordArchive::class, 'recordArchive')
            ->where('recordArchive.happening = :happening')
            ->setParameter('happening', $happening)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getByArchiveId(string $archiveId): ?RecordArchive
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('recordArchive')
            ->from(RecordArchive::class, 'recordArchive')
            ->where('recordArchive.archiveId = :archiveId')
            ->setParameter('archiveId', $archiveId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
