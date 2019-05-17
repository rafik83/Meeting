<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeAlreadyExistException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeConflictException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNegativeRowException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNotFoundException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeNotUsedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeOutDatedException;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeSoldOutException;

class ApplyPromotionCodeHandler
{
    /** @var Merger */
    private $orderMerger;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(Merger $orderMerger, \DateTimeInterface $dateTime)
    {
        $this->orderMerger = $orderMerger;
        $this->dateTime = $dateTime;
    }

    public function handle(ApplyPromotionCode $applyPromotionCode): void
    {
        $promotionCode = $applyPromotionCode->promotionCode;
        $order = $applyPromotionCode->order;

        if (null === $promotionCode) {
            throw new PromotionCodeNotFoundException();
        }

        if ($promotionCode->isOutDated($this->dateTime)) {
            throw new PromotionCodeOutDatedException();
        }

        if ($promotionCode->isSoldOut()) {
            throw new PromotionCodeSoldOutException();
        }

        if (!$this->hasAtLeastOneProductConcernedByPromotionCode($order, $promotionCode)) {
            throw new PromotionCodeNotUsedException();
        }

        if ($this->isPromotionHaveConflict($order, $promotionCode)) {
            throw new PromotionCodeConflictException();
        }

        if (!$this->isOrderRowPositive($order, $promotionCode)) {
            throw new PromotionCodeNegativeRowException();
        }

        $mergedOrder = $this->orderMerger->getMergedOrders($applyPromotionCode->order->getSheet());

        if (null !== $mergedOrder && $mergedOrder->hasPromotionCode($promotionCode)) {
            throw new PromotionCodeAlreadyExistException('This promotion code is already used');
        }

        // @todo: Add promotion code to order
    }

    private function hasAtLeastOneProductConcernedByPromotionCode(Order $order, PromotionCode $promotionCode): bool
    {
        foreach ($promotionCode->getPromotions() as $promotion) {
            if (null !== $order->getRowForProduct($promotion->getProduct())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Conflict when two promotion code offer promotion on the same product
     */
    private function isPromotionHaveConflict(Order $order, PromotionCode $promotionCode): bool
    {
        foreach ($promotionCode->getPromotions() as $promotion) {
            foreach ($order->getPromotionCodes() as $orderPromotionCode) {
                if ($orderPromotionCode->getPromotionCode()->hasPromotion($promotion->getProduct())) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isOrderRowPositive(Order $order, PromotionCode $promotionCode): bool
    {
        foreach ($promotionCode->getPromotions() as $promotion) {
            $orderRow = $order->getRowForProduct($promotion->getProduct());

            if (null !== $orderRow && $orderRow->getQuantity() > 0) {
                return true;
            }
        }

        return false;
    }
}
