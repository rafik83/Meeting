<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Invoice;

use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;

class InvoiceDataQuery
{
    /** @var  BillingInfo */
    public $billingInfos;
    
    /** @var  Sheet */
    public $sheet;
    
    /** @var  Order */
    public $order;
    
    /** @var  string */
    public $locale;
    
    /**
     * InvoiceDataQuery constructor.
     *
     * @param BillingInfo   $billingInfos
     * @param Sheet         $sheet
     * @param Order         $order
     * @param string        $locale
     */
    public function __construct(BillingInfo $billingInfos, Sheet $sheet, Order $order, $locale)
    {
        $this->billingInfos = $billingInfos;
        $this->sheet        = $sheet;
        $this->order        = $order;
        $this->locale       = $locale;
    }
}
