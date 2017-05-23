<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Token;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;

class UserEventTokenRepository implements UserEventTokenRepositoryInterface
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
    public function add(UserEventToken $userEventToken)
    {
        $this->entityManager->persist($userEventToken);
        $this->entityManager->flush($userEventToken);
    }

    /**
     * {@inheritdoc}
     */
    public function set(UserEventToken $userEventToken)
    {
        $this->entityManager->flush($userEventToken);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndUser(Event $event, User $user, $type)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('userEventToken')
            ->from(UserEventToken::class, 'userEventToken')
            ->where('userEventToken.user = :user')
            ->andWhere('userEventToken.event = :event')
            ->andWhere('userEventToken.type = :type')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->setParameter('type', $type)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
