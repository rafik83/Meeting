<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Event;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameters;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParametersRepositoryInterface;

class ExtraParametersRepository implements ExtraParametersRepositoryInterface
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
    public function add(ExtraParameters $extraParameters)
    {
        $this->entityManager->persist($extraParameters);
        $this->entityManager->flush($extraParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function set(ExtraParameters $extraParameters)
    {
        $this->entityManager->flush($extraParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ExtraParameters $extraParameters)
    {
        $this->entityManager->remove($extraParameters);
        $this->entityManager->flush($extraParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndType(Event $event, string $type)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('extra_parameters')
            ->from(ExtraParameters::class, 'extra_parameters')
            ->where('extra_parameters.event = :event')
            ->andWhere('extra_parameters.type = :type')
            ->setParameter('event', $event)
            ->setParameter('type', $type)
            ->setMaxResults(1);
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('extra_parameters')
            ->from(ExtraParameters::class, 'extra_parameters')
            ->where('extra_parameters.event = :event')
            ->setParameter('event', $event)
            ->orderBy('extra_parameters.createdAt', 'DESC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
