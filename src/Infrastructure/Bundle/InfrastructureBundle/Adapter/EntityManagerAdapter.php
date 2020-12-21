<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Adapter\EntityManagerAdapterInterface;

class EntityManagerAdapter implements EntityManagerAdapterInterface
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
    public function persist($entity)
    {
        $this->entityManager->persist($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function flush($entity = null)
    {
        $this->entityManager->flush($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->entityManager->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function detach($entity)
    {
        $this->entityManager->detach($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->entityManager->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $this->entityManager->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        $this->entityManager->rollback();
    }
}
