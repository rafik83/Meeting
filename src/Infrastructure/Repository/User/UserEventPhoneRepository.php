<?php

namespace Proximum\Vimeet\Infrastructure\Repository\User;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class UserEventPhoneRepository implements UserEventPhoneRepositoryInterface
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
    public function add(UserEventPhone $userEventPhone)
    {
        $this->entityManager->persist($userEventPhone);
        $this->entityManager->flush($userEventPhone);
    }

    /**
     * {@inheritdoc}
     */
    public function find(User $user, Event $event)
    {
        return $this
            ->getQueryBuilderByUserAndEvent($user, $event)
            ->select('user_event_phone')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findValidated(User $user, Event $event)
    {
        return $this
            ->getQueryBuilderByUserAndEvent($user, $event)
            ->select('user_event_phone')
            ->andWhere('user_event_phone.validated = true')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function set(UserEventPhone $userEventPhone)
    {
        $this->entityManager->flush($userEventPhone);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(User $user, Event $event)
    {
        $this
            ->getQueryBuilderByUserAndEvent($user, $event)
            ->delete()
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
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
            ->from(User\UserEventPhone::class, 'user_event_phone')
            ->where('user_event_phone.user = :user')
            ->andWhere('user_event_phone.event = :event')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
        ;
    }

    /**
     * @param array $blackList
     */
    public function setIntoBlackList(array $blackList)
    {
        $this->entityManager
            ->createQueryBuilder()
            ->update(UserEventPhone::class, 'userEventPhone')
            ->set('userEventPhone.stop', 'true')
            ->where('userEventPhone.stop = false')
            ->andWhere('userEventPhone.phone IN (:blackList)')
            ->setParameter('blackList', $blackList)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @param array $blackList
     */
    public function unsetFromBlackList(array $blackList)
    {
        $this->entityManager
            ->createQueryBuilder()
            ->update(UserEventPhone::class, 'userEventPhone')
            ->set('userEventPhone.stop', 'false')
            ->where('userEventPhone.stop = true')
            ->andWhere('userEventPhone.phone NOT IN (:blackList)')
            ->setParameter('blackList', $blackList)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findValidatedByEventAndUsers(Event $event, array $usersId): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user_event_phone')
            ->from(User\UserEventPhone::class, 'user_event_phone')
            ->where('user_event_phone.event = :event')
            ->andWhere('user_event_phone.user IN (:users)')
            ->andWhere('user_event_phone.validated = true')
            ->setParameter('event', $event)
            ->setParameter('users', $usersId);

        return $queryBuilder->getQuery()->getResult();
    }
}
