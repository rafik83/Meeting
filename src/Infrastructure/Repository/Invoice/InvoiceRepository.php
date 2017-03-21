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
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
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
    public function getAllByEvent(Event $event, array $filters = [])
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice', 'invoice.id')
            ->where('invoice.event = :event')
            ->setParameter('event', $event);

        if ($dateFilters = $filters['date']) {
            $queryBuilder
                ->andWhere('invoice.createdAt BETWEEN :beginDate and :endDate')
                ->setParameter('beginDate', $dateFilters['beginDate'])
                ->setParameter('endDate', $dateFilters['endDate'])
            ;
        }

        return $queryBuilder->getQuery()->getResult();
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
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('invoice, orders')
            ->from(Invoice::class, 'invoice')
            ->join('invoice.orders', 'orders', 'WITH', 'invoice.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->orderBy('invoice.id', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInvoiceForEventPrefix(Prefix $prefix, $year)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.prefix = :prefix')
            ->andWhere('invoice.invoicePrefix = :invoice_prefix')
            ->andWhere('invoice.invoiceYear = :invoice_year')
            ->orderBy('invoice.invoiceIncrement', 'DESC')
            ->setParameters([
                'prefix'         => $prefix,
                'invoice_prefix' => $prefix->getPrefix(),
                'invoice_year'   => $year,
            ])
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function isSheetInvoiced(Sheet $sheet)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('invoice.id')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasInvoice(Sheet $sheet)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('invoice.id')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->setMaxResults(1);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
