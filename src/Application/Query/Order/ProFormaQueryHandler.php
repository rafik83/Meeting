<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order;

use Proximum\Vimeet\Application\View\Order\ProFormaView;

class ProFormaQueryHandler
{
    /**
     * @param ProFormaQuery $proFormaQuery
     *
     * @return ProFormaView
     */
    public function handle(ProFormaQuery $proFormaQuery)
    {
        $locale = $proFormaQuery->locale;

        return new ProFormaView(
            $proFormaQuery->sheet,
            $proFormaQuery->order,
            $proFormaQuery->order->getBillingInfo(),
            $proFormaQuery->sheet->getEvent()->getLegalInformation(),
            $proFormaQuery->sheet->getEvent()->getBankInfo($locale),
            $proFormaQuery->sheet->getEvent()->getBillingAddress($locale),
            $proFormaQuery->sheet->getEvent()->getPaymentCondition($locale),
            $proFormaQuery->sheet->getEvent()->getPaymentFooter($locale)
        );
    }
}
