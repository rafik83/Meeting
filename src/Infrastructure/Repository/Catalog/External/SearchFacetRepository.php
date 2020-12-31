<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Catalog\External;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;

class SearchFacetRepository implements SearchFacetRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * SearchFacetRepository constructor.
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
    public function add(SearchFacet $searchFacet)
    {
        $this->entityManager->persist($searchFacet);
        $this->entityManager->flush($searchFacet);

        foreach ($searchFacet->getTranslations() as $translation) {
            $this->entityManager->persist($translation);
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
            ->join('searchFacet.translations', 'translations', 'WITH', 'searchFacet.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }
}
