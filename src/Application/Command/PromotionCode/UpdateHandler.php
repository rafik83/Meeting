<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class UpdateHandler
{
    /** @var PromotionCodeFactory */
    private $promotionCodeFactory;

    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    public function __construct(
        PromotionCodeFactory $promotionCodeFactory,
        PromotionCodeRepositoryInterface $promotionCodeRepository
    ) {
        $this->promotionCodeFactory = $promotionCodeFactory;
        $this->promotionCodeRepository = $promotionCodeRepository;
    }

    public function handle(Update $update): void
    {
        $promotionCode = $this->promotionCodeFactory->update(
            $update->promotionCode,
            $update->title,
            $update->code,
            $update->stock,
            $update->validUntil,
            $update->translations,
            $update->promotions
        );

        $this->promotionCodeRepository->set($promotionCode);
    }
}
