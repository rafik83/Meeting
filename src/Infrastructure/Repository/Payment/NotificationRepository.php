<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Payment;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Payment\Notification;
use Proximum\Vimeet\Domain\Repository\Payment\NotificationRepositoryInterface;

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
}
