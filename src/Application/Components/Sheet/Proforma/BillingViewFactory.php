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
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return BillingView
     */
    public function createFromSheet(Sheet $sheet, $locale)
    {
        return new BillingView(
            $this->billingInfoGuesser->getName($sheet, $locale),
            $this->billingInfoGuesser->getAddress($sheet, $locale),
            $this->billingInfoGuesser->getCity($sheet, $locale),
            $this->billingInfoGuesser->getZipcode($sheet, $locale),
            $this->billingInfoGuesser->getCountry($sheet, $locale),
            $this->billingInfoGuesser->getPhone($sheet, $locale),
            $this->billingInfoGuesser->getEmail($sheet, $locale),
            $this->billingInfoGuesser->getOrganization($sheet, $locale),
            $this->billingInfoGuesser->getVatNumber($sheet, $locale),
            $this->billingInfoGuesser->getExtra($sheet, $locale)
        );
    }

    /**
     * @param Order  $order
     * @param string $locale
     *
     * @return BillingView
     */
    public function createFromOrder(Order $order, $locale)
    {
        return new BillingView(
            $this->billingInfoGuesser->getName($order, $locale),
            $this->billingInfoGuesser->getAddress($order, $locale),
            $this->billingInfoGuesser->getCity($order, $locale),
            $this->billingInfoGuesser->getZipcode($order, $locale),
            $this->billingInfoGuesser->getCountry($order, $locale),
            $this->billingInfoGuesser->getPhone($order, $locale),
            $this->billingInfoGuesser->getEmail($order, $locale),
            $this->billingInfoGuesser->getOrganization($order, $locale),
            $this->billingInfoGuesser->getVatNumber($order, $locale),
            $this->billingInfoGuesser->getExtra($order, $locale)
        );
    }
}
