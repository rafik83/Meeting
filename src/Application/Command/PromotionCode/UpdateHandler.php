<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class UpdateHandler
{
    /**
     * @var PromotionCodeRepositoryInterface
     */
    private $promotionCodeRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param PromotionCodeRepositoryInterface $promotionCodeRepository
     */
    public function __construct(PromotionCodeRepositoryInterface $promotionCodeRepository)
    {
        $this->promotionCodeRepository = $promotionCodeRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $update->promotionCode->update($update->title, $update->code, $update->validUntil, $update->stock);

        foreach ($update->translations as $locale => $translation) {
            $update->promotionCode->translate($locale, $translation['label'], $translation['description']);
        }

        foreach ($update->promotions as $promotion) {
            $update->promotionCode->setPromotion($promotion['product'], $promotion['type'], $promotion['value']);
        }

        $this->promotionCodeRepository->set($update->promotionCode);
    }
}
