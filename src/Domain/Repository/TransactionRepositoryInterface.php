<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
    public function findByEvent(Event $event);

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
     * @param \DateTimeInterface $beginDate
     * @param \DateTimeInterface $endDate
     *
     * @return Transaction[]
     */
    public function findPaidByDateRange(\DateTimeInterface $beginDate, \DateTimeInterface $endDate);
}
