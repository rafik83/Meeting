<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;

interface TransactionRepositoryInterface
{
    /**
     * @param Sheet $sheet
     *
     * @return Transaction[]
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param Transaction $transaction
     */
    public function add(Transaction $transaction);

    /**
     * @param Transaction $transaction
     */
    public function set(Transaction $transaction);

    /**
     * @param Transaction $transaction
     */
    public function remove(Transaction $transaction);

    /**
     * @param Sheet $sheet
     *
     * @return Transaction[]
     */
    public function findPending(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return Transaction[]
     */
    public function findPaid(Sheet $sheet);

    /**
     * @param Event $event
     *
     * @return Transaction[]
     */
    public function findByEventAndEnabledSheets(Event $event);

    /**
     * @param Event $event
     * @param int[] $sheetIds
     *
     * @return Transaction[]
     */
    public function findByEventAndSheetIds(Event $event, array $sheetIds);

    /**
     * @param Event $event
     *
     * @return Transaction[]
     */
    public function findPaidByEvent(Event $event);

    /**
     * @param Event[]            $events
     * @param \DateTimeInterface $beginDate
     * @param \DateTimeInterface $endDate
     *
     * @return Transaction[]
     */
    public function getFilteredByEvents(array $events, \DateTimeInterface $beginDate, \DateTimeInterface $endDate);
}
