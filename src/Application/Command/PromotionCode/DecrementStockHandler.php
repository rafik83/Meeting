<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class DecrementStockHandler
{
    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    public function __construct(PromotionCodeRepositoryInterface $promotionCodeRepository)
    {
        $this->promotionCodeRepository = $promotionCodeRepository;
    }

    public function handle(DecrementStock $decrementStock): void
    {
        $promotionCode = $decrementStock->promotionCode;
        $stock = $promotionCode->getStock();

        if (null === $stock || $stock === 0) {
            return;
        }

        $promotionCode->setStock($stock - 1);
        $this->promotionCodeRepository->set($promotionCode);
    }
}
