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
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeAlreadyExistException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeConflictException;
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
     * @param Sheet     $sheet
     * @param CartRow[] $rows
     * @param array     $promotionRows
     * @param int       $currentStep
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

            if ($quantity > 0) {
                $row->setProduct($product)->setQuantity($quantity);
            } else {
                $this->rows->removeElement($row);
            }

        } elseif ($quantity > 0) {
            $this->rows[] = new CartRow($this->sheet, $product, $quantity);
        }

        return $this;
    }

    /**
     * Set additionnal participant quantity
     *
     * @return Cart
     */
    public function resolveParticipantsQuantity()
    {
        $additionnal = $this->sheet->countParticipant() - $this->getIncludedParticipantQuantity();

        if ($additionnal > 0) {
            $this->setProduct($this->sheet->getPackageParticipant(), $additionnal);
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
        foreach ($this->rows as $cartRow) {
            if ($cartRow->getProduct()->isPlan()) {
                return $cartRow;
            }
        }

        return null;
    }

    /**
     * @return null|CartRow
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
        if ($product->isParticipant() || $product->isPlan() || $product->isPlanning()) {
            return $this->rows->exists(function ($key, CartRow $cartRow) use ($product) {
                return $cartRow->getProduct()->getType() === $product->getType();
            });
        }

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
     * @return CartRow
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
        if ($product->isParticipant() || $product->isPlan() || $product->isPlanning()) {
            return $this->rows->filter(function (CartRow $cartRow) use ($product) {
                return $cartRow->getProduct()->getType() === $product->getType();
            })->first();
        }

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
     * @return int|null
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
     * @param PromotionCode      $promotionCode
     * @param \DateTimeInterface $dateTime
     *
     * @throws PromotionCodeOutDatedException
     * @throws PromotionCodeSoldOutException
     * @throws PromotionCodeAlreadyExistException
     * @throws PromotionCodeNotUsedException
     * @throws PromotionCodeConflictException
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
                if ($cartRow->getQuantity() < $promotion->getQuantity()) {
                    $total += $cartRow->getQuantity() * $promotion->getDiscount();
                } else {
                    $total += $promotion->getQuantity() * $promotion->getDiscount();
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
     * @return float|int
     */
    public function getTotal()
    {
        $total = 0;
        foreach ($this->getRows() as $cartRow) {
            $total += $cartRow->getProduct()->getUnitPrice() * $cartRow->getQuantity();
        }

        return $total;
    }
}
