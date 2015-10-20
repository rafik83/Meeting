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
use Proximum\Vimeet\Domain\Repository\FormRepositoryInterface;

class FormRepository implements FormRepositoryInterface
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

    public function getTemplate($typeId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('form.template')
            ->from('Entity:Form', 'form')
            ->where('form.type = :typeId')
            ->setParameter('typeId', $typeId)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
