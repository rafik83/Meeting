<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\PromotionCodeView;

class PromotionCodeViewQueryHandler
{
    /**
     * @param PromotionCodeViewQuery $promotionCodeViewQuery
     *
     * @return PromotionCodeView
     */
    public function handle(PromotionCodeViewQuery $promotionCodeViewQuery)
    {
        $locale = $promotionCodeViewQuery->locale;

        return new PromotionCodeView(
            $promotionCodeViewQuery->promotionCode->getLabel($locale),
            $promotionCodeViewQuery->promotionCode->getDescription($locale),
            $promotionCodeViewQuery->promotionCode->getPrice(),
            $promotionCodeViewQuery->promotionCode->getQuantity(),
            $promotionCodeViewQuery->promotionCode->getOrder()->getVatMode(),
            $promotionCodeViewQuery->promotionCode->getOrder()->getCurrency()
        );
    }
}
