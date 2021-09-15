<?php

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
