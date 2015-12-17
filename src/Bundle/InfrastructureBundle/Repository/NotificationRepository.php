<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
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
    public function add(Notification $notification)
    {
        $this->entityManager->persist($notification);
        $this->entityManager->flush($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnreadByEventAndUser($eventId, User $user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('notification')
            ->from(Notification::class, 'notification')
            ->where('notification.view = false')
            ->andWhere('notification.recipient = :user')
            ->setParameter('user', $user)
            ->andWhere('notification.event = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('notification.createdAt', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }
}
