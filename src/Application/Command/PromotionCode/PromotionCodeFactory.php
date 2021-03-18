<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;
use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;
use Proximum\Vimeet\Domain\Promotion\Exception\NonUniqueCodeException;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class PromotionCodeFactory
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var UniqueCodeChecker */
    private $uniqueCodeChecker;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        UniqueCodeChecker $uniqueCodeChecker
    ) {
        $this->orderRepository = $orderRepository;
        $this->uniqueCodeChecker = $uniqueCodeChecker;
    }

    public function create(
        Event $event,
        string $title,
        string $code,
        ?int $stock,
        ?\DateTimeInterface $validUntil,
        array $translations,
        array $promotions,
        ?PromotionCodeGroup $promotionCodeGroup = null
    ): PromotionCode {
        $promotionCode = new PromotionCode(
            $event,
            $title,
            $code,
            $stock,
            $validUntil,
            $promotionCodeGroup
        );

        $this->checkUniqueCode($promotionCode);
        $this->translate($promotionCode, $translations);
        $this->setPromotions($promotionCode, $promotions);

        return $promotionCode;
    }

    public function update(
        PromotionCode $promotionCode,
        string $title,
        string $code,
        ?int $stock,
        ?\DateTimeInterface $validUntil,
        array $translations,
        array $promotions
    ): PromotionCode {
        $promotionCode->update($title, $code, $stock, $validUntil);

        $this->checkUniqueCode($promotionCode);
        $this->translate($promotionCode, $translations);

        if (!$this->orderRepository->hasOrderWithPromotionCode($promotionCode)) {
            $this->setPromotions($promotionCode, $promotions);
        }

        return $promotionCode;
    }

    private function checkUniqueCode(PromotionCode $promotionCode): void
    {
        if (!$this->uniqueCodeChecker->hasUniqueCode($promotionCode)) {
            throw new NonUniqueCodeException('This code already exists.');
        }
    }

    private function translate(PromotionCode $promotionCode, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            $promotionCode->translate($locale, $translation['label'], $translation['description']);
        }
    }

    private function setPromotions(PromotionCode $promotionCode, array $promotions): void
    {
        foreach ($promotions as $promotion) {
            $promotionCode->setPromotion(
                $promotion['product'],
                $promotion['type'],
                $promotion['value'],
                (Promotion::TYPE_VALUE_OFF === $promotion['type']) ? 1 : $promotion['quantityMax']
            );
        }

        foreach ($promotionCode->getPromotions() as $promotion) {
            if (!$this->hasPromotion($promotions, $promotion->getProduct())) {
                $promotionCode->removePromotion($promotion);
            }
        }
    }

    private function hasPromotion(array $promotions, Product $product): bool
    {
        foreach ($promotions as $promotion) {
            if ($product === $promotion['product']) {
                return true;
            }
        }

        return false;
    }
}
