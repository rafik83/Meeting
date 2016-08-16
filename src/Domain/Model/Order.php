<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\PromotionCode as ModelPromotionCode;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * "Commande"
 */
class Order
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @var bool
     */
    private $vatApplicable;

    /**
     * @var string
     */
    private $vatMode;

    /**
     * @var float
     */
    private $vatRate;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var ArrayCollection Order\Row
     */
    private $rows = [];

    /**
     * @var ArrayCollection of Order\PromotionCode
     */
    private $promotionCodes = [];

    /**
     * @var Order\BillingInfo
     */
    private $billingInfo;

    /**
     * @var string
     */
    private $groupsData;

    /**
     * @param Sheet             $sheet
     * @param bool              $vatApplicable
     * @param Order\BillingInfo $billingInfo
     * @param string            $groupsData
     * @param DateTimeInterface $createdAt
     */
    public function __construct(
        Sheet $sheet,
        $vatApplicable,
        Order\BillingInfo $billingInfo,
        $groupsData,
        DateTimeInterface $createdAt
    ) {
        $this->sheet          = $sheet;
        $this->createdAt      = $createdAt;
        $this->vatApplicable  = $vatApplicable;
        $this->billingInfo    = $billingInfo;
        $this->groupsData     = $groupsData;
        $this->vatMode        = $sheet->getEvent()->getMode();
        $this->currency       = $sheet->getEvent()->getCurrency();
        $this->vatRate        = $sheet->getEvent()->getVat();
        $this->rows           = new ArrayCollection();
        $this->promotionCodes = new ArrayCollection();
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
     * Get vat
     *
     * @return float
     *
     * @deprecated Use getVatRate instead
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
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
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
     * @return Order\BillingInfo
     */
    public function getBillingInfo()
    {
        return $this->billingInfo;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return boolean
     */
    public function isVatApplicable()
    {
        return $this->vatApplicable;
    }

    /**
     * @return string
     */
    public function getTotalVatMode()
    {
        if ($this->vatApplicable) {
            return Event::VAT_MODE_ATI;
        }

        return $this->getVatMode();
    }

    /**
     * @return array
     */
    public function getGroupsData()
    {
        return $this->groupsData;
    }

    /**
     * @return Order\Row[]
     */
    public function getRows()
    {
        return $this->rows->toArray();
    }

    /**
     * @param Order\Row $row
     *
     * @return Order
     */
    public function addRow(Order\Row $row)
    {
        $this->rows->add($row);

        return $this;
    }

    /**
     * @param Order\PromotionCode $promotionCode
     *
     * @return Order
     */
    public function addPromotionCode(Order\PromotionCode $promotionCode)
    {
        $this->promotionCodes->add($promotionCode);

        return $this;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        $total = 0;

        foreach ($this->rows->toArray() as $row) {
            $total += $row->getQuantity() * $row->getPrice();
        }

        foreach ($this->promotionCodes->toArray() as $promotionCode) {
            $total += $promotionCode->getPrice();
        }

        if ($this->vatMode === Event::VAT_MODE_ET && $this->vatApplicable) {
            $total += $total * $this->vatRate / 100;
        }

        return $total;
    }

    /**
     * @param int         $groupId
     * @param string      $locale
     * @param string|null $fallback
     *
     * @return string
     */
    public function getGroupLabel($groupId, $locale, $fallback = null)
    {
        $data = json_decode($this->groupsData, true);

        if (!isset($data[$groupId]) || !isset($data[$groupId]['translations'])) {
            return '';
        }

        if (isset($data[$groupId]['translations'][$locale])
            && isset($data[$groupId]['translations'][$locale]['label'])
        ) {
            return $data[$groupId]['translations'][$locale]['label'];
        }

        if (null !== $fallback
            && isset($data[$groupId]['translations'][$fallback])
            && isset($data[$groupId]['translations'][$fallback]['label'])
        ) {
            return $data[$groupId]['translations'][$fallback]['label'];
        }

        return '';
    }

    /**
     * @param int $groupId
     *
     * @return int|null
     */
    public function getGroupRank($groupId)
    {
        $data = json_decode($this->groupsData, true);

        return (isset($data[$groupId]) && isset($data[$groupId]['rank'])) ? $data[$groupId]['rank'] : null;
    }

    /**
     * @return array
     */
    public function getGroupsIds()
    {
        return array_keys(json_decode($this->groupsData, true));
    }

    /**
     * @param $groupId
     *
     * @return array
     */
    public function getRowForGroupId($groupId)
    {
        return array_filter($this->rows->toArray(), function (Order\Row $row) use ($groupId) {
            return $row->getGroupId() === $groupId;
        });
    }

    /**
     * @param Product $product
     *
     * @return null|Order\Row
     */
    public function getRowForProduct(Product $product)
    {
        foreach ($this->rows as $row) {
            if ($row->getProduct() === $product) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param $id
     *
     * @return null|Order\Row
     */
    public function getRowByProductId($id)
    {
        foreach ($this->rows as $row) {
            if (null !== $row->getProduct()
                && $row->getProduct()->getId() === $id
            ) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasType($type)
    {
        return null !== $this->getProductOfType($type) ? true : false;
    }

    /**
     * @param $type
     *
     * @return null|Order\Row
     */
    public function getProductOfType($type)
    {
        foreach ($this->rows->toArray() as $row) {
            if ($row->getType() === $type) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @return Order\PromotionCode[]
     */
    public function getPromotionCodes()
    {
        return $this->promotionCodes->toArray();
    }

    /**
     * @param Order\PromotionCode|ModelPromotionCode $promotionCode
     *
     * @return bool
     */
    public function hasPromotionCode($promotionCode)
    {
        foreach ($this->promotionCodes as $promoCode) {
            if ($promotionCode instanceof ModelPromotionCode
                && $promoCode->getPromotionCode() === $promotionCode
            ) {
                return true;
            } elseif ($promotionCode instanceof Order\PromotionCode
                && $promoCode === $promotionCode
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Product $product
     *
     * @return null|Order\Row
     */
    public function getOrderRowForProduct(Product $product)
    {
        foreach ($this->rows as $orderRow) {
            if ($orderRow->getProduct() === $product) {
                return $orderRow;
            }
        }

        return null;
    }

    /**
     * @return int
     */
    public function countParticipant()
    {
        $participant = 0;
        foreach($this->rows as $orderRow) {
            if (null !== $orderRow->getProduct()
                && $orderRow->getProduct()->getType() === Product::TYPE_PARTICIPANT
            ) {
                $participant += $orderRow->getQuantity();
            }
        }

        return $participant;
    }

    /**
     * @param $product
     *
     * @return bool
     */
    public function hasPromotionCodeForProduct($product)
    {
        foreach ($this->promotionCodes as $promotionCode) {
            if ($promotionCode->getPromotionCode()->hasPromotion($product)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get product plan in order
     *
     * @return null|Product
     */
    public function getPlan()
    {
        foreach($this->rows as $row) {
            if ($row->getType() === Product::TYPE_PLAN) {
                return $row->getProduct();
            }
        }

        return null;
    }
}
