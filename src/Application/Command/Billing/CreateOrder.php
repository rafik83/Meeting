<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Billing;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Cart;
use Proximum\Vimeet\Domain\Model\Sheet;

class CreateOrder
{
    /**
     * @var Cart
     */
    public $cart;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $proFormaTemplate;

    /**
     * @var array
     */
    public $packageData;

    /**
     * @var array
     */
    public $packageTemplate;

    /**
     * @var array
     */
    public $billingData;

    /**
     * @var array
     */
    public $billingTemplate;

    /**
     * @var DateTimeInterface
     */
    public $createdAt;

    /**
     * @var string
     */
    public $paymentMode;

    /**
     * @param Cart              $cart
     * @param Sheet             $sheet
     * @param string            $state
     * @param string            $proFormaTemplate
     * @param array             $packageData
     * @param array             $packageTemplate
     * @param array             $billingData
     * @param array             $billingTemplate
     * @param DateTimeInterface $createdAt
     */
    public function __construct(
        Cart $cart,
        Sheet $sheet,
        $state,
        $proFormaTemplate,
        array $packageData,
        array $packageTemplate,
        array $billingData,
        array $billingTemplate,
        DateTimeInterface $createdAt
    ) {
        $this->cart             = $cart;
        $this->sheet            = $sheet;
        $this->state            = $state;
        $this->proFormaTemplate = $proFormaTemplate;
        $this->packageData      = $packageData;
        $this->packageTemplate  = $packageTemplate;
        $this->billingData      = $billingData;
        $this->billingTemplate  = $billingTemplate;
        $this->createdAt        = $createdAt;
    }
}
