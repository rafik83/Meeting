<?php

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\PromotionCodeView;
use Proximum\Vimeet\Application\View\Package\Summary\PromotionProductRowView;
use Proximum\Vimeet\Domain\Order\DiscountCalculator;

class PromotionCodeViewQueryHandler
{
    /** @var DiscountCalculator */
    private $discountCalculator;

    /**
     * @param DiscountCalculator $discountCalculator
     */
    public function __construct(
        DiscountCalculator $discountCalculator
    ) {
        $this->discountCalculator = $discountCalculator;
    }

    /**
     * @param PromotionCodeViewQuery $promotionCodeViewQuery
     *
     * @return PromotionCodeView
     */
    public function handle(PromotionCodeViewQuery $promotionCodeViewQuery): PromotionCodeView
    {
        $locale                   = $promotionCodeViewQuery->locale;
        $promotionProductRowViews = [];
        $promotions               = $promotionCodeViewQuery->promotionCode->getPromotionCode()->getPromotions();

        foreach ($promotions as $promotion) {
            $orderRow = $promotionCodeViewQuery->order->getRowForProduct($promotion->getProduct());

            if (null !== $orderRow) {
                $promotionProductRowViews[] = new PromotionProductRowView(
                    $promotion,
                    $promotion->getProduct()->getName(),
                    $promotion->getType(),
                    $promotion->getValue(),
                    $orderRow->getQuantity(),
                    $promotion->getProduct()->getVat(),
                    $this->discountCalculator->getDiscountForProduct(
                        $promotionCodeViewQuery->order,
                        $promotionCodeViewQuery->promotionCode,
                        $promotion->getProduct()
                    )
                );
            }
        }

        return new PromotionCodeView(
            $promotionCodeViewQuery->promotionCode->getId(),
            $promotionCodeViewQuery->promotionCode->getLabel($locale),
            $promotionCodeViewQuery->promotionCode->getDescription($locale),
            $promotionCodeViewQuery->promotionCode->getPrice(),
            $promotionCodeViewQuery->promotionCode->getOrder()->getVatMode(),
            $promotionCodeViewQuery->promotionCode->getOrder()->getCurrency(),
            $promotionProductRowViews
        );
    }
}
