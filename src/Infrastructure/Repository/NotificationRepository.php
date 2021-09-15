<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\Sheet;
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
    public function set(Notification $notification)
    {
        $this->entityManager->flush($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function findByType(Sheet $sheet, $type)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('notification')
            ->from(Notification::class, 'notification')
            ->where('notification.sheet = :sheet')
            ->andWhere('notification.type = :type')
            ->setParameter('sheet', $sheet)
            ->setParameter('type', $type)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function removeByType(Sheet $sheet, $type)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(Notification::class, 'notification')
            ->where('notification.sheet = :sheet')
            ->andWhere('notification.type = :type')
            ->setParameter('sheet', $sheet)
            ->setParameter('type', $type);

        $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function sheetHasNotification(Sheet $sheet)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('notification.id')
            ->from(Notification::class, 'notification')
            ->where('notification.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->setMaxResults(1);

        return 1 === count($queryBuilder->getQuery()->getResult());
    }
}
