<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Order;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;

class RowRepository implements RowRepositoryInterface
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
    public function remove(Row $row)
    {
        $this->entityManager->remove($row);
        $this->entityManager->flush($row);
    }

    /**
     * @param Row $row
     */
    public function set(Row $row)
    {
        $this->entityManager->flush($row);
    }
}
