<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Event;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;

class ExtraDataRepository implements ExtraDataRepositoryInterface
{
    /** @var EntityManager */
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
    public function add(ExtraData $extraData)
    {
        $this->entityManager->persist($extraData);
        $this->entityManager->flush($extraData);
    }

    /**
     * {@inheritdoc}
     */
    public function set(ExtraData $extraData)
    {
        $this->entityManager->flush($extraData);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraDataForEvent(Event $event, string $name): ?ExtraData
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('extraData')
            ->from(ExtraData::class, 'extraData')
            ->where('extraData.event = :event AND extraData.name = :name')
            ->setParameter('event', $event)
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int $extraDataId): ?ExtraData
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('extraData')
            ->from(ExtraData::class, 'extraData')
            ->where('extraData.id = :id')
            ->setParameter('id', $extraDataId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
