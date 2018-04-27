<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Transaction;

use Proximum\Vimeet\Application\View\Transaction\TransactionView;

class TransactionListViewQuery
{
    /**
     * @var TransactionView[]
     */
    public $transactionsView;

    /**
     * @var string
     */
    public $adminLocale;

    /**
     * TransactionListViewQuery constructor.
     *
     * @param TransactionView[] $transactionsView
     * @param string            $adminLocale
     */
    public function __construct(array $transactionsView, $adminLocale)
    {
        $this->transactionsView = $transactionsView;
        $this->adminLocale      = $adminLocale;
    }
}
