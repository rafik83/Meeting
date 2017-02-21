<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Invoice;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * InvoiceRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

}
