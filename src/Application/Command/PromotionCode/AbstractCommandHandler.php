<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;
use Proximum\Vimeet\Domain\Promotion\Exception\NonUniqueCodeException;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

abstract class AbstractCommandHandler
{
    /**
     * @var PromotionCodeRepositoryInterface
     */
    protected $promotionCodeRepository;

    /**
     * @var UniqueCodeChecker
     */
    protected $uniqueCodeChecker;

    /**
     * CreateHandler constructor.
     *
     * @param PromotionCodeRepositoryInterface $promotionCodeRepository
     * @param UniqueCodeChecker                $uniqueCodeChecker
     */
    public function __construct(
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        UniqueCodeChecker $uniqueCodeChecker
    ) {
        $this->promotionCodeRepository = $promotionCodeRepository;
        $this->uniqueCodeChecker       = $uniqueCodeChecker;
    }

    /**
     * @param PromotionCode $promotionCode
     */
    protected function checkUniqueCode(PromotionCode $promotionCode)
    {
        if (!$this->uniqueCodeChecker->hasUniqueCode($promotionCode)) {
            throw new NonUniqueCodeException('This code already exists.');
        }
    }

    /**
     * @param PromotionCode   $promotionCode
     * @param AbstractCommand $command
     */
    protected function translate(PromotionCode $promotionCode, AbstractCommand $command)
    {
        foreach ($command->translations as $locale => $translation) {
            $promotionCode->translate($locale, $translation['label'], $translation['description']);
        }
    }

    /**
     * @param PromotionCode   $promotionCode
     * @param AbstractCommand $command
     */
    protected function setPromotions(PromotionCode $promotionCode, AbstractCommand $command)
    {
        foreach ($command->promotions as $promotion) {
            $promotionCode->setPromotion(
                $promotion['product'],
                $promotion['type'],
                $promotion['value'],
                (Promotion::TYPE_VALUE_OFF === $promotion['type']) ? 1 : $promotion['quantityMax']
            );
        }

        foreach ($promotionCode->getPromotions() as $promotion) {
            if (!$command->hasPromotion($promotion->getProduct())) {
                $promotionCode->removePromotion($promotion);
            }
        }
    }
}
