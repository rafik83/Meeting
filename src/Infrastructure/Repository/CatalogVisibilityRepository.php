<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;

class CatalogVisibilityRepository implements CatalogVisibilityRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * CatalogVisibilityRepository constructor.
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
    public function add(CatalogVisibility $catalogVisibility)
    {
        $this->entityManager->persist($catalogVisibility);
        $this->entityManager->flush($catalogVisibility);
    }

    /**
     * {@inheritdoc}
     */
    public function set(CatalogVisibility $catalogVisibility)
    {
        $this->entityManager->flush($catalogVisibility);

        foreach ($catalogVisibility->getMessageTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getByEvent(Event $event)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('catalogVisibility')
            ->from(CatalogVisibility::class, 'catalogVisibility')
            ->where('catalogVisibility.event = :event')
            ->setParameter('event', $event)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
