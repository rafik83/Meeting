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

class TransactionViewQueryHandler
{
    /**
     * @param TransactionViewQuery $query
     *
     * @return TransactionView
     */
    public function handle(TransactionViewQuery $query)
    {
        return new TransactionView(
        
        );
    }
}
