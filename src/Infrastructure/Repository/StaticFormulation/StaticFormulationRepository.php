<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\StaticFormulation;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;

class StaticFormulationRepository implements StaticFormulationRepositoryInterface
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

    public function add(StaticFormulation $staticFormulation): void
    {
        $this->entityManager->persist($staticFormulation);
        $this->entityManager->flush($staticFormulation);
    }

    public function set(StaticFormulation $staticFormulation): void
    {
        $this->entityManager->flush($staticFormulation);
    }

    /**
     * @param Event $event
     *
     * @return StaticFormulation[]
     */
    public function findByEvent(Event $event): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('static_formulation')
            ->from(StaticFormulation::class, 'static_formulation')
            ->where('static_formulation.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Event  $event
     * @param string $key
     *
     * @return StaticFormulation[]
     */
    public function findByEventAndKey(Event $event, string $key): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('static_formulation')
            ->from(StaticFormulation::class, 'static_formulation')
            ->where('static_formulation.event = :event')
            ->andWhere('static_formulation.staticFormulationKey = :key')
            ->setParameter('event', $event)
            ->setParameter('key', $key)
            ->getQuery()
            ->getResult()
            ;
    }

    public function remove(StaticFormulation $staticFormulation): void
    {
        $this->entityManager->remove($staticFormulation);
        $this->entityManager->flush($staticFormulation);
    }
}
