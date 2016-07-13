<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Payment;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;

class TotalToPay
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @var VatApplicable
     */
    private $vatApplicable;

    /**
     * @param CartManager   $cartManager
     * @param VatApplicable $vatApplicable
     */
    public function __construct(CartManager $cartManager, VatApplicable $vatApplicable)
    {
        $this->cartManager   = $cartManager;
        $this->vatApplicable = $vatApplicable;
    }

    /**
     * @param Sheet $sheet
     *
     * @throws \Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException
     *
     * @return float
     */
    public function getTotal(Sheet $sheet)
    {
        $cart          = $this->cartManager->getCart($sheet);
        $vatApplicable = $this->vatApplicable->onCart($cart);
        $total         = $cart->getTotal() + $cart->getTotalDiscount();
        $vatToPay      = 0;

        if ($total < 0) {
            return 0;
        }

        if ($vatApplicable) {
            $vatToPay = ($total * $cart->getSheet()->getEvent()->getVat()) / 100;
        }

        return $total + $vatToPay;
    }
}
