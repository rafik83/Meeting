<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order\Specification;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Cart;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Application\Components\Sheet\BillingInfoGuesser;
use Symfony\Bundle\SecurityBundle\Tests\Functional\Bundle\AclBundle\Entity\Car;

class VatApplicable
{
    /**
     * @var BillingInfoGuesser
     */
    private $billingInfoGuesser;

    /**
     * @var array
     */
    private $europeanCountries;

    /**
     * VatApplicable constructor.
     *
     * @param BillingInfoGuesser $billingInfoGuesser
     * @param array             $europeanCountries
     */
    public function __construct(BillingInfoGuesser $billingInfoGuesser, array $europeanCountries)
    {
        $this->billingInfoGuesser = $billingInfoGuesser;
        $this->europeanCountries = $europeanCountries;
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function onOrder(Order $order)
    {
        return $this->isApplicable(
            $order->getVatMode(),
            $order->getSheet()->getEvent()->getPaymentAddress()->getCountry(),
            $this->billingInfoGuesser->getCountry($order),
            $this->billingInfoGuesser->getVatNumber($order)
        );
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function onCart(Cart $cart)
    {
        return $this->isApplicable(
            $cart->getSheet()->getEvent()->getMode(),
            $cart->getSheet()->getEvent()->getPaymentAddress()->getCountry(),
            $this->billingInfoGuesser->getCountry($cart),
            $this->billingInfoGuesser->getVatNumber($cart)
        );
    }

    /**
     * @param string $mode
     * @param string $eventCountry
     * @param string $billingCountry
     * @param string $vatNumber
     *
     * @return bool
     */
    private function isApplicable($mode, $eventCountry, $billingCountry, $vatNumber)
    {
        if ($mode === Event::VAT_MODE_ATI) {
            return false;
        }

        // Billing country and event country are the same
        if (strtoupper($billingCountry) === strtoupper($eventCountry)) {
            return true;
        }

        // Billing country is in the EU and there is not billing vat number
        if (in_array($billingCountry, $this->europeanCountries) && !$vatNumber) {
            return true;
        }

        return false;
    }
}
