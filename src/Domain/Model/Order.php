<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\PromotionCode as ModelPromotionCode;
use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Order\Numero\Generator as NumeroGenerator;

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
     * @var \DateTimeInterface
     */
    private $createdAt;

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
     * @var string
     */
    private $groupsData;

    /**
     * @var boolean
     */
    private $cancelled = false;

    /**
     * @var Invoice|null
     */
    private $invoice;

    /**
     * @param Sheet              $sheet
     * @param string             $groupsData
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Sheet $sheet,
        $groupsData,
        \DateTimeInterface $createdAt
    ) {
        $this->sheet          = $sheet;
        $this->createdAt      = $createdAt;
        $this->groupsData     = $groupsData;
        $this->currency       = $sheet->getEvent()->getCurrency();
        $this->vatRate        = $sheet->getEvent()->getVat();
        $this->rows           = new ArrayCollection();
        $this->promotionCodes = new ArrayCollection();
        $this->cancelled      = false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNumero()
    {
        return NumeroGenerator::generate($this);
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
     * Get Event vatMode
     *
     * @return string
     */
    public function getVatMode()
    {
        return $this->getSheet()->getEvent()->getMode();
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
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @deprecated use OrderVatView::isVatApplicable
     */
    public function isVatApplicable()
    {
        throw new \Exception('Order::isVatApplicable() is a deprecated method');
    }

    /**
     * @deprecated
     */
    public function getTotalVatMode()
    {
        throw new \Exception('Order::getTotalVatMode() is a deprecated method');
    }

    /**
     * @return string
     */
    public function getGroupsData()
    {
        return $this->groupsData;
    }

    /**
     * @return Row[]
     */
    public function getRows()
    {
        return $this->rows->toArray();
    }

    /**
     * @param Row $row
     *
     * @return Order
     */
    public function addRow(Row $row)
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
     * @param Row $customRow
     *
     * @return Order
     */
    public function addCustomRow(Row $customRow)
    {
        $this->rows->add($customRow);

        return $this;
    }

    /**
     * @param Row $rowToRemove
     *
     * @return self
     */
    public function removeRow(Row $rowToRemove)
    {
        foreach ($this->rows as $key => $row) {
            if ($row->getId() === $rowToRemove->getId() && $row->getQuantity() === 0) {
                $this->rows->remove($key);
                return $this;
            }
        }

        return $this;
    }

    /**
     * @param Row $customRow
     *
     * @return self
     */
    public function removeCustomRow(Row $customRow)
    {
        return $this->removeRow($customRow);
    }

    /**
     * @return float
     */
    public function getTotalWithoutVat()
    {
        $total = 0;

        /** @var Row $row */
        foreach ($this->rows->toArray() as $row) {
            $total += $row->getQuantity() * $row->getPrice();
        }

        /** @var Order\PromotionCode $promotionCode */
        foreach ($this->promotionCodes->toArray() as $promotionCode) {
            $total += $promotionCode->getPrice();
        }

        return $total;
    }

    /**
     * @deprecated use OrderVatView::vatAmount
     */
    public function getVatAmount()
    {
        throw new \Exception('Order::getVatAmount() is a deprecated method');
    }

    /**
     * @deprecated use OrderVatView::totalWithVat
     */
    public function getTotalWithVat()
    {
        throw new \Exception('Order::getTotalWithVat() is a deprecated method');
    }

    /**
     * @deprecated use OrderVatView::totalWithVat
     */
    public function getTotal()
    {
        throw new \Exception('Order::getTotal() is a deprecated method');
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
     * @return false|Order\Row[]
     */
    public function getProductRowsForGroupId($groupId)
    {
        return array_filter($this->rows->toArray(), function (Order\Row $row) use ($groupId) {
            return $row->isProduct() && $row->getGroupId() === $groupId;
        });
    }

    /**
     * @param int $groupId
     *
     * @return false|Order\Row[]
     */
    public function getCustomRowsForGroupId($groupId)
    {
        return array_filter($this->rows->toArray(), function (Order\Row $row) use ($groupId) {
            return !$row->isProduct() && $row->getGroupId() === $groupId && !$row->hasParentRow();
        });
    }

    /**
     * @param Row $parentRow
     *
     * @return false|Order\Row[]
     */
    public function getCustomRowsForProduct(Row $parentRow)
    {
        return array_filter($this->rows->toArray(), function (Order\Row $row) use ($parentRow) {
            return !$row->isProduct() && null !== $row->getParentRow() && $parentRow->getId() === $row->getParentRow()->getId();
        });
    }

    /**
     * @return false|Order\Row[]
     */
    public function getRowsWithoutParent()
    {
        return array_filter($this->rows->toArray(), function (Order\Row $row) {
            return !$row->hasParentRow();
        });
    }

    /**
     * @return false|Order\Row[]
     */
    public function getRowsWithParent()
    {
        return array_filter($this->rows->toArray(), function (Order\Row $row) {
            return $row->hasParentRow();
        });
    }

    /**
     * @param Product|null $product
     *
     * @return null|Order\Row
     */
    public function getRowForProduct(Product $product = null)
    {
        if (null === $product) {
            return null;
        }

        foreach ($this->rows as $row) {
            if (null !== $row->getProduct() && $row->getProduct() === $product) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param null|int $id
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
     * @return int
     */
    public function countParticipant()
    {
        $participant = 0;
        foreach ($this->rows as $orderRow) {
            if (null !== $orderRow->getProduct()
                && $orderRow->getProduct()->getType() === Product::TYPE_PARTICIPANT
            ) {
                $participant += $orderRow->getQuantity();
            }
        }

        return $participant;
    }

    /**
     * @return int
     */
    public function countPlanning()
    {
        $planning = 0;

        /** @var Row $orderRow */
        foreach ($this->rows as $orderRow) {
            if (null !== $orderRow->getProduct()
                && $orderRow->getProduct()->getType() === Product::TYPE_PLANNING
            ) {
                $planning += $orderRow->getQuantity();
            }
        }

        $plan = $this->getPlan();

        if ($plan instanceof Product && $plan->getType() === Product::TYPE_PLAN) {
            $planning += $plan->getIncludedPlanningQuantity();
        }

        return $planning;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function hasPromotionCodeForProduct(Product $product)
    {
        /** @var Order\PromotionCode $promotionCode */
        foreach ($this->promotionCodes->toArray() as $promotionCode) {
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
        foreach ($this->rows as $row) {
            if ($row->getType() === Product::TYPE_PLAN) {
                return $row->getProduct();
            }
        }

        return null;
    }

    /**
     * @return boolean
     */
    public function isCancelled()
    {
        return $this->cancelled;
    }

    /**
     * Set cancelled
     */
    public function cancel()
    {
        $this->cancelled = true;
    }

    /**
     * @return Invoice|null
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param Invoice $invoice
     */
    public function setInvoice(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return bool
     */
    public function hasInvoice()
    {
        return $this->getInvoice() === null ? false : true;
    }

    /**
     * @param Sheet              $sheet
     * @param \DateTimeInterface $dateTime
     *
     * @return Order
     */
    public static function createFromSheet(Sheet $sheet, \DateTimeInterface $dateTime)
    {
        return new self(
            $sheet,
            '[]',
            $dateTime
        );
    }
}
