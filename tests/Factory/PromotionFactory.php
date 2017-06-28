<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;

class PromotionFactory
{
    /**
     * @return Promotion
     */
    public static function createPromotion()
    {
        $event   = EventFactory::createEvent();
        $title   = 'Title';
        $code    = 'PROMOCODE';
        $product = new Product($event, 'test', 'test', 'test', 1.3, 2, 4, 6, true, new \DateTime(), false);

        return new Promotion(new PromotionCode($event, $title, $code), $product, 'test', 1.4);
    }
}
