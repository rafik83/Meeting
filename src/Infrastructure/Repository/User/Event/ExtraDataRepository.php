<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\User\Event;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;

class ExtraDataRepository implements ExtraDataRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * {@inheritdoc}
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
    public function getExtraDataForEventAndName(Event $event, string $name): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('extraData')
            ->from(ExtraData::class, 'extraData')
            ->where('extraData.event = :event')
            ->andWhere('extraData.name = :name')
            ->setParameter('event', $event)
            ->setParameter('name', $name);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getForEventNameOlderThanDate(
        Event $event,
        string $name,
        \DateTimeInterface $dateTime
    ): array {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('extraData', 'user')
            ->from(ExtraData::class, 'extraData')
            ->join('extraData.user', 'user', 'WITH', 'extraData.event = :event AND extraData.name = :name AND extraData.updatedAt < :date')
            ->where('extraData.event = :event')
            ->setParameter('event', $event)
            ->setParameter('name', $name)
            ->setParameter('date', $dateTime);

        return $queryBuilder->getQuery()->getResult();
    }
}
