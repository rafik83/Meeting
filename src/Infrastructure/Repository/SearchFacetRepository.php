<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\SearchFacetRepositoryInterface;

class SearchFacetRepository implements SearchFacetRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(SearchFacet $searchFacet)
    {
        $this->entityManager->persist($searchFacet);
        $this->entityManager->flush($searchFacet);

        foreach ($searchFacet->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(SearchFacet $searchFacet)
    {
        $this->entityManager->flush($searchFacet);

        foreach ($searchFacet->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('searchFacet', 'translations')
            ->from(SearchFacet::class, 'searchFacet')
            ->where('searchFacet.event = :event')
            ->join('searchFacet.translations', 'translations')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }
}
