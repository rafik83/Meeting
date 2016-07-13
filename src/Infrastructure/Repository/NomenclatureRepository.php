<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class NomenclatureRepository implements NomenclatureRepositoryInterface
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
    public function getAll()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('nomenclature')
            ->from(Nomenclature::class, 'nomenclature', 'nomenclature.id')
            ->orderBy('nomenclature.title', 'ASC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('nomenclature')
            ->from(Nomenclature::class, 'nomenclature', 'nomenclature.id')
            ->where('nomenclature.event = :event')
            ->setParameter('event', $event)
            ->orderBy('nomenclature.title', 'ASC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findGlobals()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('nomenclature')
            ->from(Nomenclature::class, 'nomenclature', 'nomenclature.id')
            ->where('nomenclature.event IS NULL')
            ->orderBy('nomenclature.title', 'ASC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('nomenclature')
            ->from(Nomenclature::class, 'nomenclature')
            ->where('nomenclature.id = :id')
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Nomenclature $nomenclature
     */
    public function add(Nomenclature $nomenclature)
    {
        $this->entityManager->persist($nomenclature);
        $this->entityManager->flush($nomenclature);
    }

    /**
     * @param Nomenclature $nomenclature
     */
    public function set(Nomenclature $nomenclature)
    {
        $this->entityManager->flush($nomenclature);
    }

    /**
     * @param Nomenclature $nomenclature
     * @param Event        $event
     *
     * @return Nomenclature|null
     */
    public function findClone($nomenclature, $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('nomenclature')
            ->from(Nomenclature::class, 'nomenclature', 'nomenclature.id')
            ->andWhere('nomenclature.original = :nomenclature')
            ->setParameter('nomenclature', $nomenclature)
            ->andWhere('nomenclature.event = :event')
            ->setParameter('event', $event)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
