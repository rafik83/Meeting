<?php

namespace Proximum\Vimeet\Application\Query\PromotionCode\Batch;

use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class CanBeUpdatable
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function isSatisfiableBy(PromotionCodeGroup $promotionCodeGroup): bool
    {
        foreach ($promotionCodeGroup->getPromotionCodes() as $promotionCode) {
            if ($this->orderRepository->hasOrderWithPromotionCode($promotionCode)) {
                return false;
            }
        }

        return true;
    }
}
