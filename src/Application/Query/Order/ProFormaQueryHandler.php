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
     * @var SummaryQueryHandler
     */
    private $summaryQueryHandler;

    /**
     * @param SummaryQueryHandler $summaryQueryHandler
     */
    public function __construct(SummaryQueryHandler $summaryQueryHandler)
    {
        $this->summaryQueryHandler = $summaryQueryHandler;
    }

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
            $this->summaryQueryHandler->handle(new SummaryQuery($proFormaQuery->sheet, $proFormaQuery->order, $locale)),
            $proFormaQuery->sheet->getEvent()->getLegalInformation(),
            $proFormaQuery->sheet->getEvent()->getBankInfo($locale),
            $proFormaQuery->sheet->getEvent()->getBillingAddress($locale),
            $proFormaQuery->sheet->getEvent()->getPaymentCondition($locale),
            $proFormaQuery->sheet->getEvent()->getPaymentFooter($locale)
        );
    }
}
