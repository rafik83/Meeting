<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode\Batch;

use Proximum\Vimeet\Application\Command\PromotionCode\PromotionCodeFactory;
use Proximum\Vimeet\Domain\Repository\PromotionCodeGroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class UpdateHandler
{
    /** @var PromotionCodeFactory */
    private $promotionCodeFactory;

    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    /** @var PromotionCodeGroupRepositoryInterface */
    private $promotionCodeGroupRepository;

    public function __construct(
        PromotionCodeFactory $promotionCodeFactory,
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        PromotionCodeGroupRepositoryInterface $promotionCodeGroupRepository
    ) {
        $this->promotionCodeFactory = $promotionCodeFactory;
        $this->promotionCodeRepository = $promotionCodeRepository;
        $this->promotionCodeGroupRepository = $promotionCodeGroupRepository;
    }

    public function handle(Update $update): void
    {
        foreach ($update->promotionCodeGroup->getPromotionCodes() as $promotionCode) {
            $this->promotionCodeRepository->set(
                $this->promotionCodeFactory->update(
                    $promotionCode,
                    $update->title,
                    $promotionCode->getCode(),
                    $update->stock,
                    $update->validUntil,
                    $update->translations,
                    $update->promotions
                )
            );
        }

        $update->promotionCodeGroup->update(
            $update->title,
            $update->stock,
            $update->validUntil
        );
        $this->promotionCodeGroupRepository->set($update->promotionCodeGroup);
    }
}
