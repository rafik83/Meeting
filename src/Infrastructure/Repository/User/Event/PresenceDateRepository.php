<?php

namespace Proximum\Vimeet\Infrastructure\Repository\User\Event;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\PresenceDate;
use Proximum\Vimeet\Domain\Repository\User\Event\PresenceDateRepositoryInterface;

class PresenceDateRepository implements PresenceDateRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(PresenceDate $presenceDate): void
    {
        $this->entityManager->persist($presenceDate);
        $this->entityManager->flush($presenceDate);
    }

    public function remove(PresenceDate $presenceDate): void
    {
        $this->entityManager->remove($presenceDate);
        $this->entityManager->flush($presenceDate);
    }

    public function getByUserAndEvent(User $user, Event $event): ?PresenceDate
    {
        return $this->entityManager->createQueryBuilder()
            ->select('presenceDate')
            ->from(PresenceDate::class, 'presenceDate')
            ->where('presenceDate.event = :event')
            ->andWhere('presenceDate.user = :user')
            ->setParameters([
                'event' => $event,
                'user' => $user,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
