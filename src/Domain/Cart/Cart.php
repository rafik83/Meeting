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
use Hautelook\AliceBundle\Tests\SymfonyApp\TestBundle\Entity\Prod;
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
     * @var CartRow[]
     */
    private $rows;

    /**
     * Cart constructor.
     *
     * @param Sheet     $sheet
     * @param CartRow[] $rows
     */
    public function __construct(Sheet $sheet, array $rows)
    {
        $this->sheet = $sheet;
        $this->rows  = new ArrayCollection($rows);
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

        if ($additionnal > 0 && $row = $this->getParticipantRow()) {
            $row->setQuantity($additionnal);
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
     * @return CartRow
     */
    public function getParticipantRow()
    {
        return $this->rows->filter(function (CartRow $cartRow) {
            return $cartRow->getProduct()->isParticipant();
        })->first();
    }

    /**
     * @return CartRow
     */
    public function getPlanningRow()
    {
        return $this->rows->filter(function (CartRow $cartRow) {
            return $cartRow->getProduct()->isPlanning();
        })->first();
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
}
