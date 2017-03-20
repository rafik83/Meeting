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
            $query->event->getId(),
            $query->event->getTraceableName(),
            $query->sheet->getOwner()->getId(),
            'society',
            $query->transaction->getDate(),
            $query->transaction->getMode(),
            $query->transaction->getReference(),
            $query->payment->getDetails(),
            $query->transaction->getAmount(),
            $query->event->getPaymentAddress()->getCountry(),
            $query->event->getVat()
        );
    }
}
