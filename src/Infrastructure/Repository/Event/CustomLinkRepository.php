<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Event;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\CustomLinkRepositoryInterface;

class CustomLinkRepository implements CustomLinkRepositoryInterface
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findByEvent(Event $event): array
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('custom_link')
            ->from(Event\CustomLink::class, 'custom_link')
            ->where('custom_link.event = :event')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
