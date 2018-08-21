<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\User\Event\Scan;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;

class ScanRepository implements ScanRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /** @param EntityManager $entityManager */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(Scan $scan): void
    {
        $this->entityManager->persist($scan);
        $this->entityManager->flush($scan);
    }
}
