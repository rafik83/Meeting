<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Sheet;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;

class GroupRepository implements GroupRepositoryInterface
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
     * @param int $id
     *
     * @return null|Group
     */
    public function getById($id): ?Group
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheetsGroup')
            ->from(Group::class, 'sheetsGroup')
            ->where('sheetsGroup.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEventAndManager(Event $event, User $manager): ?Group
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheetsGroup')
            ->from(Group::class, 'sheetsGroup')
            ->where('sheetsGroup.manager = :manager AND sheetsGroup.event = :event')
            ->setParameter('manager', $manager)
            ->setParameter('event', $event)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllByEventOrderedByTitle(Event $event): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('sheetsGroup')
            ->from(Group::class, 'sheetsGroup')
            ->where('sheetsGroup.event = :event')
            ->setParameter('event', $event)
            ->orderBy('sheetsGroup.title', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheetsGroup')
            ->from(Group::class, 'sheetsGroup')
            ->where('sheetsGroup.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(Group $group): void
    {
        $this->entityManager->persist($group);
        $this->entityManager->flush($group);
    }

    /**
     * @param Group $group
     */
    public function set(Group $group): void
    {
        $this->entityManager->flush($group);
    }

    /**
     * {@inheritdoc}
     */
    public function getByUserAndEvent(User $user, Event $event): ?Group
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheetsGroup')
            ->from(Group::class, 'sheetsGroup')
            ->join('sheetsGroup.sheets', 'sheet', 'WITH', 'sheetsGroup.event = :event')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user')
            ->setParameters([
                'event' => $event,
                'user'  => $user,
            ])
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findDuplicatedGroupInEvent(Group $originGroup, Event $event): ?Group
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheetsGroup')
            ->from(Group::class, 'sheetsGroup')
            ->where('sheetsGroup.event = :event AND sheetsGroup.duplicatedFrom = :originGroup')
            ->setParameters([
                'event' => $event,
                'originGroup' => $originGroup,
            ])
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
