<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
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
            ->select('transaction, payment')
            ->from(Transaction::class, 'transaction')
            ->leftJoin('transaction.payment', 'payment') // Payment is a oneToOne and is always fetch even when no used.
            ->where('transaction.sheet = :sheet')
            ->andWhere('transaction.hidden = false')
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
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(Transaction::class, 'transaction')
            ->where('transaction = :transaction')
            ->setParameter('transaction', $transaction)
            ->getQuery()
            ->execute();

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
            ->andWhere('transaction.hidden = false')
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
            ->andWhere('transaction.hidden = false')
            ->andWhere('transaction.state = :state')
            ->setParameter('sheet', $sheet)
            ->setParameter('state', Transaction::STATE_PAID);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndEnabledSheets(Event $event)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('transaction, sheet')
            ->from(Transaction::class, 'transaction')
            ->join('transaction.sheet', 'sheet', 'WITH', 'sheet.event = :event AND sheet.enable = true AND transaction.hidden = false')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndSheetIds(Event $event, array $sheetIds)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('transaction, sheet, payment')
            ->from(Transaction::class, 'transaction')
            ->join(
                'transaction.sheet',
                'sheet',
                'WITH',
                'sheet.id IN (:sheetIds) AND sheet.event = :event AND transaction.hidden = false'
            )
            ->leftJoin('transaction.payment', 'payment') // Payment is a oneToOne and is always fetch even when no used.
            ->setParameter('event', $event)
            ->setParameter('sheetIds', $sheetIds)
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
            ->andWhere('transaction.hidden = false')
            ->setParameter('state', Transaction::STATE_PAID)
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getFilteredByEvents(array $events, \DateTimeInterface $beginDate, \DateTimeInterface $endDate)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('transaction, payment, sheet')
            ->from(Transaction::class, 'transaction')
            ->join(
                'transaction.sheet',
                'sheet',
                'WITH',
                'sheet.event IN (:events) AND transaction.date BETWEEN :beginDate and :endDate AND transaction.state = :state AND transaction.hidden = false'
            )
            ->leftJoin('transaction.payment', 'payment')
            ->orderBy('transaction.date')
            ->setParameters([
                'events' => $events,
                'state' => Transaction::STATE_PAID,
                'beginDate' => $beginDate,
                'endDate' => $endDate,
            ]);

        return $queryBuilder->getQuery()->getResult();
    }
}
