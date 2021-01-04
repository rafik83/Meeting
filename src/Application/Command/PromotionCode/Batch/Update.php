<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode\Batch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;

class Update implements Command
{
    /** @var PromotionCodeGroup */
    public $promotionCodeGroup;

    /** @var string */
    public $title;

    /** @var \DateTimeInterface */
    public $validUntil;

    /** @var null|int */
    public $stock;

    /** @var array */
    public $translations = [];

    /** @var array */
    public $promotions = [];

    public function __construct(PromotionCodeGroup $promotionCodeGroup)
    {
        $this->promotionCodeGroup = $promotionCodeGroup;
        $this->title = $promotionCodeGroup->getTitle();
        $this->validUntil = $promotionCodeGroup->getValidUntil();
        $this->stock = $promotionCodeGroup->getStock();
        $promotionCodes = $promotionCodeGroup->getPromotionCodes();
        $promotionCode = reset($promotionCodes);

        if (false === $promotionCode) {
            throw new \LogicException('No promotion code exists in this group');
        }

        foreach ($promotionCode->getEvent()->getLocales() as $locale) {
            $this->translations[$locale] = [
                'label' => $promotionCode->getLabel($locale),
                'description' => $promotionCode->getDescription($locale),
            ];
        }

        foreach ($promotionCode->getPromotions() as $promotion) {
            $this->promotions[] = [
                'product' => $promotion->getProduct(),
                'type' => $promotion->getType(),
                'value' => $promotion->getValue(),
                'quantityMax' => $promotion->getQuantityMax(),
            ];
        }
    }
}
