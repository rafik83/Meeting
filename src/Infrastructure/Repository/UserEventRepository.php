<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;

class UserEventRepository implements UserEventRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**s
     * @param EntityManager $entityManager
     * @param Paginator     $paginator
     */
    public function __construct(EntityManager $entityManager, Paginator $paginator)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(UserEvent $userEvent)
    {
        $this->entityManager->persist($userEvent);
        $this->entityManager->flush($userEvent);
    }

    /**
     * {@inheritdoc}
     */
    public function set(UserEvent $userEvent)
    {
        $this->entityManager->flush($userEvent);
    }

    /**
     * {@inheritdoc}
     */
    public function getByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user, event, user_event.type')
            ->from(UserEvent::class, 'user', 'user.id')
            ->join('user_event.event', 'event', 'WITH', 'event = :event')
            ->setParameter('event', $event)
            ->join('user_event.user', 'user', 'WITH', 'user_event.user = user.id')
            ->orderBy('user.email', 'ASC');

        return array_keys($queryBuilder->getQuery()->getResult());
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getByUser(User $user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user, event, user_event.type')
            ->from(UserEvent::class, 'user', 'user.id')
            ->join('user_event', 'user', 'WITH', 'user = :user')
            ->setParameter('user', $user)
            ->orderBy('user.email', 'ASC');

        return array_keys($queryBuilder->getQuery()->getResult());
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return UserEvent
     */
    public function getUserEvent(User $user, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user_event')
            ->from(UserEvent::class, 'user_event', 'user_event.id')
            ->where('user_event.user = :user')
            ->andWhere('user_event.event = :event')
            ->setParameter('user', $user)
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
