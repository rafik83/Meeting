<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Product;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;

class QuantityMinGuesser
{
    /**
     * @var Merger
     */
    private $merger;

    /**
     * QuantityMinGuesser constructor.
     *
     * @param Merger $merger
     */
    public function __construct(Merger $merger)
    {
        $this->merger = $merger;
    }

    /**
     * @param Sheet   $sheet
     * @param Product $product
     * @param int     $quantity
     *
     * @return false|int
     */
    public function getMinProduct(Sheet $sheet, Product $product, $quantity)
    {
        if (!$sheet->hasNotCancelledOrders()) {
            return 0;
        }

        $order = $this->merger->merge($sheet->getNotCancelledOrders());

        if ($order->hasPromotionCodeForProduct($product)) {
            if ($orderRow = $order->getRowForProduct($product)) {
                if ($quantity < $orderRow->getQuantity()) {
                    return false;
                }
            }
        }

        return 0;
    }
}
