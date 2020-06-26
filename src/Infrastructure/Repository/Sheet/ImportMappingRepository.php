<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Sheet;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\ImportMapping;
use Proximum\Vimeet\Domain\Repository\Sheet\ImportMappingRepositoryInterface;

class ImportMappingRepository implements ImportMappingRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(ImportMapping $importMapping): void
    {
        $this->entityManager->persist($importMapping);
        $this->entityManager->flush($importMapping);
    }

    public function update(ImportMapping $importMapping): void
    {
        $this->entityManager->flush($importMapping);
    }

    public function hasExistingMappingWithTitle(Event $event, string $title): bool
    {
        return null !== $this
            ->entityManager
            ->createQueryBuilder()
            ->select('import_mapping.id')
            ->from(ImportMapping::class, 'import_mapping')
            ->where('import_mapping.event = :event')
            ->andWhere('import_mapping.title = :title')
            ->setParameter('event', $event)
            ->setParameter('title', $title)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getById(int $id): ?ImportMapping
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('import_mapping')
            ->from(ImportMapping::class, 'import_mapping')
            ->where('import_mapping.id = :id')
            ->addOrderBy('import_mapping.createdAt')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }


    public function getByEvent(Event $event): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('import_mapping')
            ->from(ImportMapping::class, 'import_mapping')
            ->where('import_mapping.event = :event')
            ->addOrderBy('import_mapping.createdAt')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }
}
