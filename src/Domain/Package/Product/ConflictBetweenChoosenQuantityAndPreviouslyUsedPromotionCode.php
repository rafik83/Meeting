<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Product;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;

class ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode
{
    /** @var Merger */
    private $merger;

    public function __construct(Merger $merger)
    {
        $this->merger = $merger;
    }

    public function hasConflict(?Order $order, Sheet $sheet, Product $product, int $quantity): bool
    {
        if (null === $order) {
            if (!$sheet->hasNotCancelledOrders()) {
                return false;
            }

            $order = $this->merger->merge($sheet->getNotCancelledOrders());
        }

        if ($order->hasPromotionCodeForProduct($product) && $orderRow = $order->getRowForProduct($product)) {
            if ($quantity < $orderRow->getQuantity()) {
                return true;
            }
        }

        return false;
    }
}
