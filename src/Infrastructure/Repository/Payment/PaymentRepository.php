<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Payment;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\Payment\PaymentRepositoryInterface;

class PaymentRepository implements PaymentRepositoryInterface
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
     * @param int $id
     *
     * @return null|Payment
     */
    public function findById($id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('payment')
            ->from(Payment::class, 'payment')
            ->where('payment = :id')
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
    
    /**
     * {@inheritdoc}
     */
    public function findPaidByDateRangeAndCrossEvent(
        \DateTimeInterface $beginDate,
        \DateTimeInterface $endDate,
        array $events
    ) {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('payment')
            ->from(Transaction::class, 'transaction')
            ->join(Sheet::class, 'sheet', 'WITH', 'sheet.event IN (:events)')
            ->leftJoin(Payment::class, 'payment', 'WITH', 'payment.transaction = transaction')
            ->where('transaction.date BETWEEN :beginDate and :endDate')
            ->andWhere('transaction.state = :state')
            ->andWhere('payment.id IS NOT NULL')
            ->groupBy('transaction.id')
            ->setParameters([
                'state' => Transaction::STATE_PAID,
                'beginDate' => $beginDate,
                'endDate' => $endDate,
                'events' => $events,
            ]);
        
        return $queryBuilder->getQuery()->getResult();
    }
}
