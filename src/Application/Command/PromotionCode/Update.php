<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\PromotionCode;

class Update
{
    /**
     * @var PromotionCode
     */
    public $promotionCode;

    /**
     * @var string
     */
    public $title;

    /**
     * @var \DateTimeInterface
     */
    public $validUntil;

    /**
     * @var int
     */
    public $stock;

    /**
     * @var array
     */
    public $translations;

    /**
     * @var array
     */
    public $promotions;

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
                'description' => $promotionCode->getDescription($locale)
            ];
        }

        foreach ($promotionCode->getPromotions() as $promotion) {
            $this->promotions[] = [
                'product' => $promotion->getProduct(),
                'type'    => $promotion->getType(),
                'value'   => $promotion->getValue(),
            ];
        }
    }
}
