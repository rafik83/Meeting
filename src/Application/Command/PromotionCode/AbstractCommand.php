<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Model\Product;

abstract class AbstractCommand
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $code;

    /**
     * @var \DateTimeInterface
     */
    public $validUntil;

    /**
     * @var int
     */
    public $stock;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var array
     */
    public $promotions = [];

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function hasPromotion(Product $product)
    {
        foreach ($this->promotions as $promotion) {
            if ($product === $promotion['product']) {
                return true;
            }
        }

        return false;
    }
}
