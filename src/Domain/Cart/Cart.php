<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Cart;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeAlreadyExistException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeConflictException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNegativeRowException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNotUsedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeOutDatedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeSoldOutException;

class Cart
{
    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var ArrayCollection of CartRow
     */
    private $rows;

    /**
     * @var ArrayCollection of PromotionCodeRow
     */
    private $promotionCodeRows;

    /**
     * @var int
     */
    private $currentStep;

    /**
     * Cart constructor.
     *
     * @param Sheet              $sheet
     * @param CartRow[]          $rows
     * @param PromotionCodeRow[] $promotionRows
     * @param int                $currentStep
     */
    public function __construct(Sheet $sheet, array $rows, array $promotionRows, $currentStep = null)
    {
        $this->sheet             = $sheet;
        $this->rows              = new ArrayCollection($rows);
        $this->promotionCodeRows = new ArrayCollection($promotionRows);
        $this->currentStep       = $currentStep;
    }

    /**
     * Get sheet
     *
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @param Product $product
     * @param int     $quantity
     *
     * @return Cart
     */
    public function setProduct(Product $product, $quantity)
    {
        if ($this->hasProduct($product)) {
            $row = $this->getRow($product);

            if (0 === $quantity) {
                $this->rows->removeElement($row);
            } else {
                $row->setProduct($product)->setQuantity($quantity);
            }

        } elseif (0 !== $quantity) {
            $this->rows[] = new CartRow($this->sheet, $product, $quantity);
        }

        return $this;
    }

    /**
     * Get how many participant are included.
     *
     * @return int
     */
    public function getIncludedParticipantQuantity()
    {
        return array_reduce($this->getRows(), function ($carry, CartRow $row) {
            return $carry + $row->getProduct()->getIncludedParticipantQuantity();
        }, 0);
    }

    /**
     * @return null|CartRow
     */
    public function getPlanRow()
    {
        /** @var CartRow $cartRow */
        foreach ($this->rows as $cartRow) {
            if ($cartRow->getProduct()->isPlan()) {
                return $cartRow;
            }
        }

        return null;
    }

    /**
     * @return null|CartRow
     *
     * @deprecated use getParticipantRows()
     */
    public function getParticipantRow()
    {
        foreach ($this->rows as $cartRow) {
            if ($cartRow->getProduct()->isParticipant()) {
                return $cartRow;
            }
        }

        return null;
    }

    /**
     * @return CartRow[]
     */
    public function getParticipantRows(): array
    {
        return array_filter($this->getRows(), function(CartRow $cartRow) {
            return $cartRow->getProduct()->isParticipant();
        });
    }

    /**
     * @return null|CartRow
     */
    public function getPlanningRow()
    {
        foreach ($this->rows as $cartRow) {
            if ($cartRow->getProduct()->isPlanning()) {
                return $cartRow;
            }
        }

        return null;
    }

    /**
     * @return ArrayCollection CartRow[]
     */
    public function getOptionsRow()
    {
        return $this->rows->filter(function (CartRow $cartRow) {
            return $cartRow->getProduct()->isOption();
        });
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function hasProduct(Product $product)
    {
        return $this->rows->exists(function ($key, CartRow $cartRow) use ($product) {
            return $cartRow->getProduct() === $product;
        });
    }

    /**
     * @param PromotionCode $promotionCode
     *
     * @return bool
     */
    public function hasPromotionCode(PromotionCode $promotionCode)
    {
        return $this->promotionCodeRows->exists(
            function ($key, PromotionCodeRow $promotionCodeRow) use ($promotionCode) {
                return $promotionCodeRow->getPromotionCode() === $promotionCode;
            });
    }

    /**
     * @param Product $product
     *
     * @return CartRow|null
     */
    public function getCartRowForProduct(Product $product)
    {
        foreach ($this->rows as $cartRow) {
            if ($cartRow->getProduct() === $product) {
                return $cartRow;
            }
        }

        return null;
    }

    /**
     * @param Product $product
     *
     * @return false|CartRow
     */
    public function getRow(Product $product)
    {
        return $this->rows->filter(function (CartRow $cartRow) use ($product) {
            return $cartRow->getProduct() === $product;
        })->first();
    }

    /**
     * @return CartRow[]
     */
    public function getRows()
    {
        return $this->rows->toArray();
    }

    /**
     * @return PromotionCodeRow[]
     */
    public function getPromotionCodeRows()
    {
        return $this->promotionCodeRows->toArray();
    }

    /**
     * Clear the cart
     */
    public function clear()
    {
        $this->rows->clear();
    }

    /**
     * Clear the cart
     */
    public function clearOptions()
    {
        foreach ($this->getOptionsRow() as $row) {
            $this->rows->removeElement($row);
        }
    }

    /**
     * @return null|int
     */
    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    /**
     * @param int $currentStep
     *
     * @return Cart
     */
    public function setCurrentStep($currentStep)
    {
        $this->currentStep = $currentStep;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        $rows = $this->rows->toArray();

        return empty($rows) ? 0 : array_reduce(
            $rows,
            function ($carry, CartRow $row) {
                return $carry + ($row->getQuantity() * $row->getProduct()->getUnitPrice());
            },
            0
        );
    }

    /**
     * @param PromotionCode      $promotionCode
     * @param \DateTimeInterface $dateTime
     *
     * @throws PromotionCodeException
     *
     * @return Cart
     */
    public function apply(PromotionCode $promotionCode, \DateTimeInterface $dateTime)
    {
        if ($promotionCode->isOutDated($dateTime)) {
            throw new PromotionCodeOutDatedException();
        }

        if ($promotionCode->isSoldOut()) {
            throw new PromotionCodeSoldOutException();
        }

        if ($this->hasPromotionCode($promotionCode)) {
            throw new PromotionCodeAlreadyExistException();
        }

        if (!$this->cartRowProductInPromotionCode($promotionCode)) {
            throw new PromotionCodeNotUsedException();
        }

        if ($this->isPromotionHaveConflict($promotionCode)) {
            throw new PromotionCodeConflictException();
        }

        if (!$this->isCartRowPositive($promotionCode)) {
            throw new PromotionCodeNegativeRowException();
        }

        $this->promotionCodeRows->add(new PromotionCodeRow($this->sheet, $promotionCode));

        return $this;
    }

    /**
     * @param PromotionCode $promotionCode
     *
     * @return bool
     */
    public function cartRowProductInPromotionCode(PromotionCode $promotionCode)
    {
        foreach ($promotionCode->getPromotions() as $promotion) {
            if (null !== $this->getCartRowForProduct($promotion->getProduct())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Conflict when two promotion code offer promotion on the same product
     *
     * @param PromotionCode $promotionCode
     *
     * @return bool
     */
    public function isPromotionHaveConflict(PromotionCode $promotionCode)
    {
        foreach ($promotionCode->getPromotions() as $promotion) {
            foreach ($this->getPromotionCodeRows() as $promotionCodeRow) {
                if ($promotionCodeRow->getPromotionCode()->hasPromotion($promotion->getProduct())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param PromotionCode $promotionCode
     *
     * @return bool
     */
    public function isCartRowPositive(PromotionCode $promotionCode)
    {
        foreach ($promotionCode->getPromotions() as $promotion) {
            if ($cartRow = $this->getCartRowForProduct($promotion->getProduct())) {
                if (!$cartRow->isNegative()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get total discount for a specific promotion code
     *
     * @param PromotionCode $promotionCode
     *
     * @return float|int
     */
    public function getDiscount(PromotionCode $promotionCode)
    {
        $total = 0;
        foreach ($promotionCode->getPromotions() as $promotion) {
            if (($cartRow = $this->getCartRowForProduct($promotion->getProduct())) !== null) {

                // don't apply promo code on cart row negative quantity
                if ($cartRow->getQuantity() < 0) {
                    continue;
                }

                // don't use promotion quantity max if promotion type value off
                if (Promotion::TYPE_VALUE_OFF === $promotion->getType()) {
                    $total -= $promotion->getDiscount();
                } elseif ($cartRow->getQuantity() < $promotion->getQuantityMax()
                    || null === $promotion->getQuantityMax()
                ) {
                    $total -= $cartRow->getQuantity() * $promotion->getDiscount();
                } else {
                    $total -= $promotion->getQuantityMax() * $promotion->getDiscount();
                }
            }
        }

        return $total;
    }

    /**
     * Get total discount of all promotion code on the cart
     *
     * @return float|int
     */
    public function getTotalDiscount()
    {
        $total = 0;
        foreach ($this->getPromotionCodeRows() as $promotionCodeRow) {
            $total += $this->getDiscount($promotionCodeRow->getPromotionCode());
        }

        return $total;
    }

    /**
     * @param CartRow $cartRow
     */
    public function removeRow(CartRow $cartRow)
    {
        $this->rows->removeElement($cartRow);
    }

    /**
     * Merge and resolve cart row quantity and order merged row quantity
     *
     * @param Product    $product
     * @param null|Order $order
     *
     * @return int
     */
    public function getOrderCartQuantity(Product $product, Order $order = null)
    {
        $mergedQuantity = 0;
        $cartRow        = $this->getCartRowForProduct($product);

        // handle first order
        if (null !== $cartRow) {
            $mergedQuantity = $cartRow->getQuantity();
        }

        // handle new order
        if (null !== $order && $product = $order->getRowForProduct($product)) {
            $orderQuantity  = $product->getQuantity();
            $mergedQuantity = $orderQuantity;

            if (null !== $cartRow) {
                $mergedQuantity = $orderQuantity + $cartRow->getQuantity();
            }
        }

        return $mergedQuantity;
    }
}
