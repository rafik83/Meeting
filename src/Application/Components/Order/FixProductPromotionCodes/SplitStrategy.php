<?php

namespace Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes;

use Proximum\Vimeet\Application\Components\Order\OrderHelper;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Money\AmountFormatter;

/**
 * Class SplitStrategy
 *
 * (5th strategy) When unique promotion code has to be sliced in many one
 *
 * @package Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes
 */
class SplitStrategy implements FixProductPromotionStrategyInterface
{
    /** @var OrderHelper */
    private $orderHelper;
    
    public function __construct(OrderHelper $orderHelper)
    {
        $this->orderHelper = $orderHelper;
    }
    
    /**
     * {@inheritdoc}
     */
    public function canApply(Order $order): bool
    {
        $modelPromotionCodes = $this->orderHelper->getModelPromotionCodes($order);

        foreach ($modelPromotionCodes as $modelPromotionCode) {
            $newPromotionCodeRows = $this->orderHelper->convertToPromotionCodes($order, $modelPromotionCode);
            $existingPromotionCodeRows = $this->orderHelper->getPromotionCodes($order, $modelPromotionCode);
            $promoCompareResult = $this->compare($existingPromotionCodeRows, $newPromotionCodeRows);
            if (false === $promoCompareResult) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fix(Order $order): void
    {
        if (false === $this->canApply($order)) {
            throw new \BadMethodCallException('Can\'t apply this strategy to this order');
        }

        $modelPromotionCodes = $this->orderHelper->getModelPromotionCodes($order);

        foreach ($modelPromotionCodes as $modelPromotionCode) {
            $newPromotionCodeRows = $this->orderHelper->convertToPromotionCodes($order, $modelPromotionCode);
            $existingPromotionCodeRows = $this->orderHelper->getPromotionCodes($order, $modelPromotionCode);
            $this->set($order, $existingPromotionCodeRows, $newPromotionCodeRows);
        }
    }

    /**
     * @param Order\PromotionCode[] $existingPromotionCodeRows
     * @param Order\PromotionCode[] $newPromotionCodeRows
     *
     * @return bool
     */
    private function compare(array $existingPromotionCodeRows, array $newPromotionCodeRows): bool
    {
        if (count($existingPromotionCodeRows) !== 1) {
            return false;
        }

        $existingPromotionCodeRow = $existingPromotionCodeRows[0];

        $existingPrice = $existingPromotionCodeRow->getPrice();

        $newPrice = 0;
        foreach ($newPromotionCodeRows as $newPromotionCodeRow) {
            $newPrice += $newPromotionCodeRow->getPrice();
        }

        $discountDiff = round(
            $existingPrice - $newPrice,
            2
        );

        if ($discountDiff !== 0.0) {
            return false;
        }

        $newPromotionCodeRowsSet = [];

        $sumNewPromotionCodeRowPrice = 0.0;
        foreach ($newPromotionCodeRows as $newPromotionCodeRow) {
            if (in_array($newPromotionCodeRow, $newPromotionCodeRowsSet, true)) {
                continue;
            }
            if ($this->doesNewPromoAndExistingPromoMatchWithoutPrice(
                $existingPromotionCodeRow,
                $newPromotionCodeRow
            )) {
                $newPromotionCodeRowsSet[] = $newPromotionCodeRow;
                $sumNewPromotionCodeRowPrice += $newPromotionCodeRow->getPrice();
            }
        }

        $arePriceEqual = AmountFormatter::decimalToCentsAmount($sumNewPromotionCodeRowPrice)
            === AmountFormatter::decimalToCentsAmount($existingPromotionCodeRow->getPrice());

        return $arePriceEqual && count($newPromotionCodeRowsSet) === count($newPromotionCodeRows);
    }

    /**
     * @param Order                 $order
     * @param Order\PromotionCode[] $existingPromotionCodeRows
     * @param Order\PromotionCode[] $newPromotionCodeRows
     *
     * @return void
     */
    private function set(Order $order, array $existingPromotionCodeRows, array $newPromotionCodeRows): void
    {
        $existingPromotionCodeRow = $existingPromotionCodeRows[0];

        $isFirstDone = false;
        foreach ($newPromotionCodeRows as $newPromotionCodeRow) {
            if (false === $isFirstDone) {
                $isFirstDone = true;
                $existingPromotionCodeRow->setProduct($newPromotionCodeRow->getProduct());
                $existingPromotionCodeRow->setPrice($newPromotionCodeRow->getPrice());
                continue;
            }
            $order->addPromotionCode($newPromotionCodeRow);
        }
    }

    /**
     * @param Order\PromotionCode $existingPromotionCodeRow
     * @param Order\PromotionCode $newPromotionCodeRow
     *
     * @return bool
     */
    private function doesNewPromoAndExistingPromoMatchWithoutPrice(
        Order\PromotionCode $existingPromotionCodeRow,
        Order\PromotionCode $newPromotionCodeRow
    ): bool {
        return $existingPromotionCodeRow->getVatRate() === $newPromotionCodeRow->getVatRate()
            && $existingPromotionCodeRow->getPromotionCode() === $newPromotionCodeRow->getPromotionCode()
            && $existingPromotionCodeRow->getLabel('fr') === $newPromotionCodeRow->getLabel('fr')
            && $existingPromotionCodeRow->getDescription('fr') === $newPromotionCodeRow->getDescription('fr');
    }
}
