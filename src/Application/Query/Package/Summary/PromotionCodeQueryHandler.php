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
            $totalDiscount = 0;
            // foreach all promotion for one product
            foreach ($promotionCodeRow->getPromotionCode()->getPromotions() as $promotion) {
                if (($cartRow = $cart->getCartRowForProduct($promotion->getProduct())) !== null) {
                    $totalDiscount += $promotion->getDiscount();
                }
            }

            $promotionsCodeView[] = new PromotionCodeView(
                $promotionCodeRow->getPromotionCode()->getTitle(),
                $promotionCodeRow->getPromotionCode()->getDescription($promotionCodeQuery->locale),
                $totalDiscount,
                $promotionCodeQuery->sheet->getEvent()->getCurrency(),
                $promotionCodeQuery->sheet->getEvent()->getMode()
            );
        }

        return new PromotionCodesView($promotionsCodeView);
    }
}
