<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;
use Proximum\Vimeet\Domain\Repository\SpotUnavailabilityRepositoryInterface;

class SpotUnavailabilityRepository implements SpotUnavailabilityRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * SpotUnavailabilityRepository constructor.
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
    public function add(SpotUnavailability $spotUnavailability)
    {
        $this->entityManager->persist($spotUnavailability);
        $this->entityManager->flush($spotUnavailability);
    }

    /**
     * @param Spot $spot
     */
    public function remove(Spot $spot)
    {
        $this->entityManager->createQueryBuilder()
            ->delete(SpotUnavailability::class, 'spot_unavailability')
            ->where('spot_unavailability.spot = :spot')
            ->setParameter('spot', $spot)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySpot(Spot $spot)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('spot_unavailability')
            ->from(SpotUnavailability::class, 'spot_unavailability')
            ->where('spot_unavailability.spot = :spot')
            ->setParameter('spot', $spot);

        return $queryBuilder->getQuery()->getResult();
    }
}
