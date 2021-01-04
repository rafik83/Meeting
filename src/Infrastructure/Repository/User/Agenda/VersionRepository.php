<?php

namespace Proximum\Vimeet\Infrastructure\Repository\User\Agenda;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;

class VersionRepository implements VersionRepositoryInterface
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
     * @param Version $version
     */
    public function add(Version $version)
    {
        $this->entityManager->persist($version);
        $this->entityManager->flush($version);
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return QueryBuilder
     */
    private function getQueryBuilderByUserAndEvent(User $user, Event $event)
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->from(Version::class, 'version')
            ->where('version.user = :user')
            ->andWhere('version.event = :event')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastVersionByEventAndUser(Event $event, User $user): ?Version
    {
        return $this->getQueryBuilderByUserAndEvent($user, $event)
            ->select('version')
            ->orderBy('version.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function removeVersionsOfEvents(array $events): void
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(Version::class, 'version')
            ->where('version.event IN (:events)')
            ->setParameter('events', $events)
        ;

        $queryBuilder->getQuery()->execute();
    }
}
