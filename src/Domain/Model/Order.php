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
use Proximum\Vimeet\Domain\Model\Exception\Order\NotAddedRowException;
use Proximum\Vimeet\Domain\Model\Exception\Order\RowAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Exception\Order\RowNotFoundException;

/**
 * "Commande"
 */
class Order implements BillingInfoInterface
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
     * @var array
     */
    private $packageData;

    /**
     * @var array
     */
    private $packageTemplate;

    /**
     * @var array
     */
    private $billingData;

    /**
     * @var array
     */
    private $billingTemplate;

    /**
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @var string
     */
    private $paymentMode;

    /**
     * @var string
     */
    private $vatMode;

    /**
     * @var float
     */
    private $vatRate;

    /**
     * @param Sheet             $sheet
     * @param string            $state
     * @param array             $packageData
     * @param array             $packageTemplate
     * @param array             $billingData
     * @param array             $billingTemplate
     * @param DateTimeInterface $createdAt
     * @param string            $paymentMode
     */
    public function __construct(
        Sheet $sheet,
        $state,
        array $packageData,
        array $packageTemplate,
        array $billingData,
        array $billingTemplate,
        DateTimeInterface $createdAt,
        $paymentMode
    ) {
        $this->sheet            = $sheet;
        $this->state            = $state;
        $this->packageData      = $packageData;
        $this->packageTemplate  = $packageTemplate;
        $this->billingData      = $billingData;
        $this->billingTemplate  = $billingTemplate;
        $this->createdAt        = $createdAt;
        $this->paymentMode      = $paymentMode;
        $this->vatMode          = $sheet->getEvent()->getMode();
        $this->vatRate          = $sheet->getEvent()->getVat();
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
     * @return array
     */
    public function getPackageData()
    {
        return $this->packageData;
    }

    /**
     * @return array
     */
    public function getPackageTemplate()
    {
        return $this->packageTemplate;
    }

    /**
     * @return array
     */
    public function getBillingData()
    {
        return $this->billingData;
    }

    /**
     * @return array
     */
    public function getBillingTemplate()
    {
        return $this->billingTemplate;
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

    /**
     * @deprecated Use getVatRate instead
     *
     * Get vat
     *
     * @return float
     */
    public function getVat()
    {
        return $this->getVatRate();
    }

    /**
     * Get vatMode
     *
     * @return string
     */
    public function getVatMode()
    {
        return $this->vatMode;
    }

    /**
     * Get vatRate
     *
     * @return float
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }

    /**
     * Add row to the order
     *
     * @param string $group
     * @param string $row
     * @param string $label
     * @param string $description
     * @param float  $unitPrice
     * @param int    $quantity
     */
    public function addRow($group, $row, $label, $description, $unitPrice, $quantity)
    {
        if ($this->issetRow($group, $row)) {
            throw new RowAlreadyExistsException($group, $row);
        }

        $this->setRow($group, $row, $label, $description, $unitPrice, $quantity);
    }

    /**
     * Remove a row
     *
     * @param string $group
     * @param string $row
     */
    public function removeRow($group, $row)
    {
        if (!$this->issetRow($group, $row)) {
            throw new RowNotFoundException($group, $row);
        }

        if (!$this->isAddedRow($group, $row)) {
            throw new NotAddedRowException($group, $row);
        }

        unset ($this->packageTemplate[$group]['template'][$row]);
        unset ($this->packageData[$group][$row]);
    }

    /**
     * Update row
     *
     * @param string $group
     * @param string $row
     * @param string $label
     * @param string $description
     * @param float  $unitPrice
     * @param int    $quantity
     */
    public function updateRow($group, $row, $label, $description, $unitPrice, $quantity)
    {
        if (!$this->issetRow($group, $row)) {
            throw new RowNotFoundException($group, $row);
        }

        if (!$this->isAddedRow($group, $row)) {
            throw new NotAddedRowException($group, $row);
        }

        $this->setRow($group, $row, $label, $description, $unitPrice, $quantity);
    }

    /**
     * @param string $group
     * @param string $row
     *
     * @return bool
     */
    private function issetRow($group, $row)
    {
        return isset($this->packageTemplate[$group]['template'][$row]) && isset($this->packageData[$group][$row]);
    }

    /**
     * @param $group
     * @param $row
     *
     * @return bool
     */
    private function isAddedRow($group, $row)
    {
        return $this->packageTemplate[$group]['template'][$row]['type'] === 'added_row';
    }

    /**
     * @param string $group
     * @param string $row
     * @param string $label
     * @param string $description
     * @param float  $unitPrice
     * @param int    $quantity
     */
    private function setRow($group, $row, $label, $description, $unitPrice, $quantity)
    {
        $this->packageTemplate[$group]['template'][$row] = [
            'label'       => $label,
            'description' => $description,
            'type'        => 'added_row',
            'unitPrice'   => $unitPrice,
        ];

        $this->packageData[$group][$row] = [
            'value'    => true,
            'quantity' => $quantity,
        ];
    }
}
