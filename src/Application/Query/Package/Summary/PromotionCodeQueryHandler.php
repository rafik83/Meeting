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
use Proximum\Vimeet\Application\View\Package\Summary\PromotionProductRowView;

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

            $promotionProductRowViews = [];
            foreach($promotionCodeRow->getPromotionCode()->getPromotions() as $promotion) {
                $cartRow = $cart->getCartRowForProduct($promotion->getProduct());

                // don't show row with negative quantity
                if (null !== $cartRow && !$cartRow->isNegative()) {
                    $promotionProductRowViews[] = new PromotionProductRowView(
                        $promotion,
                        $promotion->getProduct()->getTitle($promotionCodeQuery->locale),
                        $promotion->getType(),
                        $promotion->getValue(),
                        $promotion->getQuantityForCartRow($cartRow)
                    );
                }
            }

            $promotionsCodeView[] = new PromotionCodeView(
                $promotionCodeRow->getId(),
                $promotionCodeRow->getPromotionCode()->getLabel($promotionCodeQuery->locale),
                $promotionCodeRow->getPromotionCode()->getDescription($promotionCodeQuery->locale),
                $cart->getDiscount($promotionCodeRow->getPromotionCode()),
                $promotionCodeQuery->sheet->getEvent()->getCurrency(),
                $promotionCodeQuery->sheet->getEvent()->getMode(),
                $promotionProductRowViews
            );
        }

        return new PromotionCodesView($promotionsCodeView);
    }
}
