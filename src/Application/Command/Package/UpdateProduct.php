<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use DateTimeInterface;
use Proximum\Vimeet\Application\Components\Product\Products\ProductInterface;
use Proximum\Vimeet\Domain\Model\Cart;
use Proximum\Vimeet\Domain\Model\Sheet;

class UpdateProduct
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Cart
     */
    public $cart;

    /**
     * @var ProductInterface
     */
    public $product;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var array
     */
    public $productItem = [];

    /**
     * @var DateTimeInterface
     */
    public $createdAt;

    /**
     * @var bool
     */
    private $checked = true;

    /**
     * @var int
     */
    private $previousQuantity;

    /**
     * @param Sheet             $sheet
     * @param Cart              $cart
     * @param ProductInterface  $product
     * @param DateTimeInterface $createdAt
     * @param string            $locale
     * @param int               $quantity
     */
    public function __construct(
        Sheet $sheet,
        Cart $cart,
        ProductInterface $product,
        DateTimeInterface $createdAt,
        $locale,
        $quantity
    ) {
        $this->sheet                   = $sheet;
        $this->cart                    = $cart;
        $this->product                 = $product;
        $this->locale                  = $locale;
        $this->productItem['value']    = $this->checked;
        $this->productItem['quantity'] = $quantity;
        $this->previousQuantity        = $quantity;
        $this->createdAt               = $createdAt;
    }

    /**
     * @return bool
     */
    public function isChecked()
    {
        return $this->checked;
    }

    /**
     * @param bool $checked
     */
    public function setChecked($checked)
    {
        $this->productItem['value'] = $checked;
        $this->checked              = $checked;
    }

    /**
     * @return int
     */
    public function getPreviousQuantity()
    {
        return $this->previousQuantity;
    }

    /**
     * @return int
     */
    public function getNewQuantity()
    {
        return (!$this->productItem['value'] ? 0 : $this->productItem['quantity']) - $this->getPreviousQuantity();
    }

    /**
     * @return bool
     */
    public function isNegative()
    {
        return $this->getNewQuantity() < 0;
    }

    /**
     * @return bool
     */
    public function isPositive()
    {
        return $this->getNewQuantity() > 0;
    }
}
