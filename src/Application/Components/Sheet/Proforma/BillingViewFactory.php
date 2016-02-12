<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Proforma;

use Proximum\Vimeet\Application\Components\Sheet\BillingInfoGuesser;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;

class BillingViewFactory
{
    /**
     * @var BillingInfoGuesser
     */
    private $billingInfoGuesser;

    /**
     * BillingViewFactory constructor.
     *
     * @param BillingInfoGuesser $billingInfoGuesser
     */
    public function __construct(BillingInfoGuesser $billingInfoGuesser)
    {
        $this->billingInfoGuesser = $billingInfoGuesser;
    }

    /**
     * @param Sheet $sheet
     *
     * @return BillingView
     */
    public function createFromSheet(Sheet $sheet)
    {
        return new BillingView(
            $this->billingInfoGuesser->getName($sheet),
            $this->billingInfoGuesser->getAddress($sheet),
            $this->billingInfoGuesser->getCity($sheet),
            $this->billingInfoGuesser->getZipcode($sheet),
            $this->billingInfoGuesser->getCountry($sheet),
            $this->billingInfoGuesser->getPhone($sheet),
            $this->billingInfoGuesser->getEmail($sheet),
            $this->billingInfoGuesser->getOrganization($sheet),
            $this->billingInfoGuesser->getVatNumber($sheet),
            $this->billingInfoGuesser->getExtra($sheet)
        );
    }

    /**
     * @param Order $sheet
     *
     * @return BillingView
     */
    public function createFromOrder(Order $sheet)
    {
        return new BillingView(
            $this->billingInfoGuesser->getName($sheet),
            $this->billingInfoGuesser->getAddress($sheet),
            $this->billingInfoGuesser->getCity($sheet),
            $this->billingInfoGuesser->getZipcode($sheet),
            $this->billingInfoGuesser->getCountry($sheet),
            $this->billingInfoGuesser->getPhone($sheet),
            $this->billingInfoGuesser->getEmail($sheet),
            $this->billingInfoGuesser->getOrganization($sheet),
            $this->billingInfoGuesser->getVatNumber($sheet),
            $this->billingInfoGuesser->getExtra($sheet)
        );
    }
}
