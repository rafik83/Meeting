<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;

/**
 * "Commande"
 */
class Order
{
    const STATE_UNPAID = 'unpaid';
    const STATE_PAID   = 'paid';

    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $proFormaTemplate;

    /**
     * @var array
     */
    private $packageData;

    /**
     * @var array
     */
    private $billingData;

    /**
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @var string
     */
    private $paymentMode;

    /**
     * @param Sheet             $sheet
     * @param string            $state
     * @param string            $proFormaTemplate
     * @param array             $packageData
     * @param array             $billingData
     * @param DateTimeInterface $createdAt
     * @param string            $paymentMode
     */
    public function __construct(
        Sheet $sheet,
        $state,
        $proFormaTemplate,
        array $packageData,
        array $billingData,
        DateTimeInterface $createdAt,
        $paymentMode
    ) {
        $this->sheet            = $sheet;
        $this->state            = $state;
        $this->proFormaTemplate = $proFormaTemplate;
        $this->packageData      = $packageData;
        $this->billingData      = $billingData;
        $this->createdAt        = $createdAt;
        $this->paymentMode      = $paymentMode;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getProFormaTemplate()
    {
        return $this->proFormaTemplate;
    }

    /**
     * @return array
     */
    public function getPackageData()
    {
        return $this->packageData;
    }

    /**
     * @return array
     */
    public function getBillingData()
    {
        return $this->billingData;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getPaymentMode()
    {
        return $this->paymentMode;
    }
}
