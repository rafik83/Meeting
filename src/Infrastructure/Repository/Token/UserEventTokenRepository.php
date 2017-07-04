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
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

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
    public function findByEventAndUserAndType(Event $event, User $user, $type)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('userEventToken')
            ->from(UserEventToken::class, 'userEventToken')
            ->where('userEventToken.user = :user AND userEventToken.event = :event AND userEventToken.type = :type')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->setParameter('type', $type)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationStatusByUserAndType(User $user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('userEventToken.confirmed')
            ->from(UserEventToken::class, 'userEventToken')
            ->where('userEventToken.user = :user AND userEventToken.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', UserEventTokenType::AGENDA_CONFIRMATION)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
