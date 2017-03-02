<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Invoice;

use Proximum\Vimeet\Application\Query\BillingInfos\BillingInfosQuery;
use Proximum\Vimeet\Application\Query\BillingInfos\BillingInfosQueryHandler;
use Proximum\Vimeet\Application\Query\Order\SummaryQuery;
use Proximum\Vimeet\Application\Query\Order\SummaryQueryHandler;
use Proximum\Vimeet\Application\View\Invoice\InvoiceDataView;
use Proximum\Vimeet\Domain\Order\Balance;

class InvoiceDataQueryHandler
{
    /** @var  BillingInfosQueryHandler */
    public $billingInfosQueryHandler;
    
    /** @var  SummaryQueryHandler */
    public $summaryQueryHandler;
    
    /** @var  Balance */
    public $balance;
    
    /**
     * InvoiceDataQueryHandler constructor.
     *
     * @param BillingInfosQueryHandler  $billingInfosQueryHandler
     * @param SummaryQueryHandler       $summaryQueryHandler
     * @param Balance                   $balance
     */
    public function __construct(
        BillingInfosQueryHandler $billingInfosQueryHandler,
        SummaryQueryHandler      $summaryQueryHandler,
        Balance                  $balance
    ) {
        $this->billingInfosQueryHandler = $billingInfosQueryHandler;
        $this->summaryQueryHandler      = $summaryQueryHandler;
        $this->balance                  = $balance;
    }
    
    public function handle(InvoiceDataQuery $invoiceDataQuery)
    {
        return new InvoiceDataView(
            $this->summaryQueryHandler->handle(
                new SummaryQuery(
                    $invoiceDataQuery->sheet,
                    $invoiceDataQuery->order,
                    $invoiceDataQuery->locale
               )
            ),
            $this->billingInfosQueryHandler->handle(
                new BillingInfosQuery(
                    $invoiceDataQuery->billingInfos->getLastname(),
                    $invoiceDataQuery->billingInfos->getFirstname(),
                    $invoiceDataQuery->billingInfos->getFunction(),
                    $invoiceDataQuery->billingInfos->getPhone(),
                    $invoiceDataQuery->billingInfos->getMobile(),
                    $invoiceDataQuery->billingInfos->getEmail(),
                    $invoiceDataQuery->billingInfos->getCompany(),
                    $invoiceDataQuery->billingInfos->getAddress(),
                    $invoiceDataQuery->billingInfos->getVatNumber(),
                    $invoiceDataQuery->billingInfos->getGender()
                )
            ),
            $this->balance->getRemainingToPay($invoiceDataQuery->sheet)
        );
    }
}
