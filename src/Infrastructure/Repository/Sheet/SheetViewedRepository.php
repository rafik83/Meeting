<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Sheet;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Sheet\SheetViewed;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;

class SheetViewedRepository implements SheetViewedRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * SheetViewedRepository constructor.
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
    public function add(SheetViewed $sheetViewed)
    {
        $this->entityManager->persist($sheetViewed);
        $this->entityManager->flush($sheetViewed);
    }
}
