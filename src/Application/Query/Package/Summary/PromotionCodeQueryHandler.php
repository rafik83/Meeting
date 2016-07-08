<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\PromotionCodesView;
use Proximum\Vimeet\Application\View\Package\Summary\PromotionCodeView;

class PromotionCodeQueryHandler
{
    /**
     * @param PromotionCodeQuery $promotionCodeQuery
     *
     * @return PromotionCodesView
     */
    public function handle(PromotionCodeQuery $promotionCodeQuery)
    {
        $cart = $promotionCodeQuery->cart;

        $promotionsCodeView = [];

        // foreach promotion code used
        foreach ($cart->getPromotionCodeRows() as $promotionCodeRow) {
            $promotionsCodeView[] = new PromotionCodeView(
                $promotionCodeRow->getId(),
                $promotionCodeRow->getPromotionCode()->getLabel($promotionCodeQuery->locale),
                $promotionCodeRow->getPromotionCode()->getDescription($promotionCodeQuery->locale),
                $cart->getDiscount($promotionCodeRow->getPromotionCode()),
                $promotionCodeQuery->sheet->getEvent()->getCurrency(),
                $promotionCodeQuery->sheet->getEvent()->getMode()
            );
        }

        return new PromotionCodesView($promotionsCodeView);
    }
}
