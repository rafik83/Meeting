<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;

class Create
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Prefix
     */
    public $prefix;
    
    /**
     * @var int
     */
    public $total;

    /**
     * @var int
     */
    public $totalWithVat;

    /**
     * @var int
     */
    public $vatAmount;

    /**
     * Create constructor.
     *
     * @param Sheet               $sheet
     * @param OrdersToInvoiceView $ordersToInvoiceView
     */
    public function __construct(
        Sheet $sheet,
        OrdersToInvoiceView $ordersToInvoiceView
    ) {
        $this->sheet         = $sheet;
        $this->prefix        = $sheet->getEvent()->getInvoicePrefix();
        $this->total         = $ordersToInvoiceView->getTotal();
        $this->totalWithVat  = $ordersToInvoiceView->getTotalWithVat();
        $this->vatAmount     = $ordersToInvoiceView->getVatAmount();
    }
}
