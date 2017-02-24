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
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;
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
        
        return $invoice;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->orderBy('invoice.id', 'DESC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInvoiceForEventPrefix($invoicePrefix)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('invoice_number')
            ->from(Invoice::class, 'invoice')
            ->join('invoice.event', 'event')
            ->where('event.invoice_prefix = :invoice_prefix')
            ->orderBy('createdAt', 'DESC')
            ->setParameter('invoice_prefix', $invoicePrefix)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
