<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * TransactionRepository constructor.
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
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('transaction')
            ->from(Transaction::class, 'transaction')
            ->where('transaction.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->orderBy('transaction.date', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(Transaction $transaction)
    {
        $this->entityManager->persist($transaction);
        $this->entityManager->flush($transaction);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Transaction $transaction)
    {
        $this->entityManager->flush($transaction);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Transaction $transaction)
    {
        $this->entityManager->remove($transaction);
        $this->entityManager->flush($transaction);
    }

    /**
     * {@inheritdoc}
     */
    public function findPending(Sheet $sheet)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('transaction')
            ->from(Transaction::class, 'transaction')
            ->where('transaction.sheet = :sheet')
            ->andWhere('transaction.state = :state')
            ->orderBy('transaction.date', 'DESC')
            ->setParameter('sheet', $sheet)
            ->setParameter('state', Transaction::STATE_PENDING);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findPaid(Sheet $sheet)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('transaction')
            ->from(Transaction::class, 'transaction')
            ->where('transaction.sheet = :sheet')
            ->andWhere('transaction.state = :state')
            ->setParameter('sheet', $sheet)
            ->setParameter('state', Transaction::STATE_PAID);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('transaction, sheet')
            ->from(Transaction::class, 'transaction')
            ->join('transaction.sheet', 'sheet', 'WITH', 'sheet.enable = true')
            ->where('sheet.event = :event')
            ->setParameter('event', $event)
        ;

       return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findPaidByEvent(Event $event)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('transaction, sheet')
            ->from(Transaction::class, 'transaction')
            ->join('transaction.sheet', 'sheet', 'WITH', 'sheet.enable = true')
            ->where('sheet.event = :event')
            ->andWhere('transaction.state = :state')
            ->setParameter('state', Transaction::STATE_PAID)
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
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
            ->select('transaction')
            ->from(Transaction::class, 'transaction')
            ->join(Sheet::class, 'sheet', 'WITH', 'sheet.event IN (:events)')
            ->where('transaction.date BETWEEN :beginDate and :endDate')
            ->andWhere('transaction.state = :state')
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
