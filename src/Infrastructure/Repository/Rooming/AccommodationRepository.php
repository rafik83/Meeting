<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Rooming;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Repository\Rooming\AccommodationRepositoryInterface;

class AccommodationRepository implements AccommodationRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(Accommodation $accommodation): void
    {
        $this->entityManager->persist($accommodation);
        $this->entityManager->flush($accommodation);
    }

    public function update(Accommodation $accommodation): void
    {
        $this->entityManager->flush($accommodation);
    }

    /**
     * @param Event $event
     *
     * @return Accommodation[]
     */
    public function getByEvent(Event $event): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('accommodation')
            ->from(Accommodation::class, 'accommodation')
            ->where('accommodation.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }
}
