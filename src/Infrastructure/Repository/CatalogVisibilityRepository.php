<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;

class CatalogVisibilityRepository implements CatalogVisibilityRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * CatalogVisibilityRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(CatalogVisibility $catalogVisibility)
    {
        $this->entityManager->persist($catalogVisibility);
        $this->entityManager->flush($catalogVisibility);
    }
}
