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
use Proximum\Vimeet\Domain\Model\CartRow;

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

            $promotionProductRowView = [];
            foreach($promotionCodeRow->getPromotionCode()->getPromotions() as $promotion) {
                $cartRow = $cart->getCartRowForProduct($promotion->getProduct());

                if($cartRow instanceof CartRow) {
                    $promotionProductRowView[] = new PromotionProductRowView(
                        $promotion->getProduct()->getName(),
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
                $promotionProductRowView
            );
        }

        return new PromotionCodesView($promotionsCodeView);
    }
}
