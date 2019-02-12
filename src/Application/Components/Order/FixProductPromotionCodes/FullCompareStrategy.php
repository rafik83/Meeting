<?php

namespace Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes;

use Proximum\Vimeet\Application\Components\Order\OrderHelper;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Money\AmountFormatter;

/**
 * Class FullCompareStrategy
 *
 * (4th strategy) When regenerating promotion codes and there's the same promotion codes count
 *
 * @package Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes
 */
class FullCompareStrategy implements FixProductPromotionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function canApply(Order $order): bool
    {
        $modelPromotionCodes = OrderHelper::getModelPromotionCodes($order);

        foreach ($modelPromotionCodes as $modelPromotionCode) {
            $newPromotionCodeRows = OrderHelper::convertToPromotionCodes($order, $modelPromotionCode);
            $existingPromotionCodeRows = OrderHelper::getPromotionCodes($order, $modelPromotionCode);
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

        $modelPromotionCodes = OrderHelper::getModelPromotionCodes($order);

        foreach ($modelPromotionCodes as $modelPromotionCode) {
            $newPromotionCodeRows = OrderHelper::convertToPromotionCodes($order, $modelPromotionCode);
            $existingPromotionCodeRows = OrderHelper::getPromotionCodes($order, $modelPromotionCode);
            $this->set($existingPromotionCodeRows, $newPromotionCodeRows);
        }
    }

    /**
     * @param Order\PromotionCode[] $existingPromotionCodeRows
     * @param Order\PromotionCode[] $newPromotionCodeRows
     *
     * @return bool
     */
    private function compare(array $existingPromotionCodeRows, array $newPromotionCodeRows)
    {
        $existingPrice = 0;
        $existingCountElts = 0;
        foreach ($existingPromotionCodeRows as $existingPromotionCodeRow) {
            $existingPrice += $existingPromotionCodeRow->getPrice();
            $existingCountElts++;
        }

        $newPrice = 0;
        $newCountElts = 0;
        foreach ($newPromotionCodeRows as $existingPromotionCodeRow) {
            $newPrice += $existingPromotionCodeRow->getPrice();
            $newCountElts++;
        }

        $discountDiff = round(
            $existingPrice - $newPrice,
            2
        );

        if ($discountDiff !== 0.0) {
            return false;
        }

        if ($existingCountElts !== $newCountElts) {
            return false;
        }

        $newPromotionCodeRowsSet = [];
        foreach ($existingPromotionCodeRows as $existingPromotionCodeRow) {
            foreach ($newPromotionCodeRows as $newPromotionCodeRow) {
                if (in_array($newPromotionCodeRow, $newPromotionCodeRowsSet, true)) {
                    continue;
                }
                if ($this->doesNewPromoAndExistingPromoMatch($existingPromotionCodeRow, $newPromotionCodeRow)) {
                    $newPromotionCodeRowsSet[] = $newPromotionCodeRow;
                    break;
                }
            }
        }

        return count($newPromotionCodeRowsSet) === count($newPromotionCodeRows);
    }

    /**
     * @param Order\PromotionCode[] $existingPromotionCodeRows
     * @param Order\PromotionCode[] $newPromotionCodeRows
     */
    private function set(array $existingPromotionCodeRows, array $newPromotionCodeRows)
    {
        $newPromotionCodeRowsSet = [];
        foreach ($existingPromotionCodeRows as $existingPromotionCodeRow) {
            foreach ($newPromotionCodeRows as $newPromotionCodeRow) {
                if (in_array($newPromotionCodeRow, $newPromotionCodeRowsSet, true)) {
                    continue;
                }
                if ($this->doesNewPromoAndExistingPromoMatch($existingPromotionCodeRow, $newPromotionCodeRow)) {
                    $newPromotionCodeRowsSet[] = $newPromotionCodeRow;
                    $existingPromotionCodeRow->setProduct($newPromotionCodeRow->getProduct());
                    break;
                }
            }
        }
    }

    /**
     * @param Order\PromotionCode $existingPromotionCodeRow
     * @param Order\PromotionCode $newPromotionCodeRow
     *
     * @return bool
     */
    private function doesNewPromoAndExistingPromoMatch(
        Order\PromotionCode $existingPromotionCodeRow,
        Order\PromotionCode $newPromotionCodeRow
    ): bool {
        return AmountFormatter::decimalToCentsAmount($existingPromotionCodeRow->getPrice())
            === AmountFormatter::decimalToCentsAmount($newPromotionCodeRow->getPrice())
            && $existingPromotionCodeRow->getVatRate() === $newPromotionCodeRow->getVatRate()
            && $existingPromotionCodeRow->getPromotionCode() === $newPromotionCodeRow->getPromotionCode()
            && $existingPromotionCodeRow->getLabel('fr') === $newPromotionCodeRow->getLabel('fr')
            && $existingPromotionCodeRow->getDescription('fr') === $newPromotionCodeRow->getDescription('fr');
    }
}
