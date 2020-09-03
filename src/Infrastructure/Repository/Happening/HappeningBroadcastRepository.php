<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Happening;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\HappeningBroadcast;
use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;

class HappeningBroadcastRepository implements HappeningBroadcastRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(HappeningBroadcast $happeningBroadcast): void
    {
        $this->entityManager->persist($happeningBroadcast);
        $this->entityManager->flush($happeningBroadcast);
    }

    public function update(HappeningBroadcast $happeningBroadcast): void
    {
        $this->entityManager->flush($happeningBroadcast);
    }

    public function deleteForHappening(Happening $happening): void
    {
        $this->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(HappeningBroadcast::class, 'broadcast')
            ->where('broadcast.happening = :happening')
            ->setParameter('happening', $happening)
        ;
    }

    public function getByHappening(Happening $happening): ?HappeningBroadcast
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('broadcast')
            ->from(HappeningBroadcast::class, 'broadcast')
            ->where('broadcast.happening = :happening')
            ->setParameter('happening', $happening)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
