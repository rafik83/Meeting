<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\ParticipantImport;
use Proximum\Vimeet\Domain\Repository\ParticipantImportRepositoryInterface;

class ParticipantImportRepository implements ParticipantImportRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * ParticipantImportRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(ParticipantImport $participantImport)
    {
        $this->entityManager->persist($participantImport);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('participant_import')
            ->from(ParticipantImport::class, 'participant_import')
            ->where('participant_import.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
