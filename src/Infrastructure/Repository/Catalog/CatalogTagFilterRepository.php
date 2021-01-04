<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Catalog;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Catalog\CatalogTagFilterRepositoryInterface;

class CatalogTagFilterRepository implements CatalogTagFilterRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getByEventAndType(Event $event, string $type): array
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('catalogTagFilter', 'translations')
            ->from(CatalogTagFilter::class, 'catalogTagFilter')
            ->join('catalogTagFilter.translations', 'translations')
            ->where('catalogTagFilter.event = :event')
            ->andWhere('catalogTagFilter.type = :type')
            ->setParameters([
                'event' => $event,
                'type' => $type,
            ]);

        return $queryBuilder->getQuery()->getResult();
    }

    public function removeByEventAndType(Event $event, string $type): void
    {
        $this->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(CatalogTagFilter::class, 'catalogTagFilter')
            ->where('catalogTagFilter.event = :event')
            ->andWhere('catalogTagFilter.type = :type')
            ->setParameters([
                'event' => $event,
                'type' => $type,
            ])
            ->getQuery()
            ->execute();
    }

    public function add(CatalogTagFilter $catalogTagFilter): void
    {
        $this->entityManager->persist($catalogTagFilter);
        $this->entityManager->flush($catalogTagFilter);
    }
}
