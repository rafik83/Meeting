<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

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
}
