<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Model\PromotionCode;

class Update extends AbstractCommand
{
    /**
     * @var PromotionCode
     */
    public $promotionCode;

    /**
     * Update constructor.
     *
     * @param PromotionCode $promotionCode
     */
    public function __construct(PromotionCode $promotionCode)
    {
        $this->promotionCode = $promotionCode;
        $this->title         = $promotionCode->getTitle();
        $this->code          = $promotionCode->getCode();
        $this->validUntil    = $promotionCode->getValidUntil();
        $this->stock         = $promotionCode->getStock();

        foreach ($promotionCode->getEvent()->getLocales() as $locale) {
            $this->translations[$locale] = [
                'label'       => $promotionCode->getLabel($locale),
                'description' => $promotionCode->getDescription($locale),
            ];
        }

        foreach ($promotionCode->getPromotions() as $promotion) {
            $this->promotions[] = [
                'product'     => $promotion->getProduct(),
                'type'        => $promotion->getType(),
                'value'       => $promotion->getValue(),
                'quantityMax' => $promotion->getQuantityMax(),
            ];
        }
    }
}
