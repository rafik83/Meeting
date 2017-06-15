<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\User;

use Doctrine\ORM\EntityManager;
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
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user_event_phone')
            ->from(User\UserEventPhone::class, 'user_event_phone')
            ->where('user_event_phone.user = :user')
            ->andWhere('user_event_phone.event = :event')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function set(UserEventPhone $userEventPhone)
    {
        $this->entityManager->flush($userEventPhone);
    }
}
