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
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\CartRow;

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
     * @var int
     */
    private $currentStep;

    /**
     * Cart constructor.
     *
     * @param Sheet     $sheet
     * @param CartRow[] $rows
     * @param int       $currentStep
     */
    public function __construct(Sheet $sheet, array $rows, $currentStep = null)
    {
        $this->sheet       = $sheet;
        $this->rows        = new ArrayCollection($rows);
        $this->currentStep = $currentStep;
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
     * @return int|mixed
     */
    public function getTotal()
    {
        $rows = $this->rows->toArray();

        return empty($rows) ? 0 : array_reduce($rows, function ($carry, CartRow $row) {
            return $carry + ($row->getQuantity() * $row->getProduct()->getUnitPrice());
        }, 0);
    }
}
