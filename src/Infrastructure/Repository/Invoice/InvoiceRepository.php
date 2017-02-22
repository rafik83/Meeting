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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
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

    /**
     * {@inheritdoc}
     */
    public function add(Invoice $invoice)
    {
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInvoiceForEventPrefix($invoicePrefix)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('number')
            ->from(Invoice::class, 'invoice')
            ->leftJoin(Event::class, 'event', 'ON', 'invoice.event = event')
            ->where('event.invoice_prefix = :invoice_prefix')
            ->setParameter('invoice_prefix', $invoicePrefix)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
