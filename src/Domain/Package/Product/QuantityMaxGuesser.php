<?php

namespace Proximum\Vimeet\Domain\Package\Product;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;

class QuantityMaxGuesser
{
    /** @var CartManager */
    private $cartManager;

    /** @var Merger */
    private $merger;

    /** @var Order[] indexed by Sheet Id */
    private $cachedMergedOrderBySheetId = [];

    public function __construct(CartManager $cartManager, Merger $merger)
    {
        $this->cartManager = $cartManager;
        $this->merger = $merger;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int|float
     */
    public function getMaxPlanning(Sheet $sheet)
    {
        $planning = $sheet->getPackage()->getPlanning();

        if (!$planning) {
            return 0;
        }

        $remainingQuantity = INF;
        $selectedPlan = $this->getSelectedPlan($sheet);

        if ($selectedPlan) {
            $includedPlanningProduct = $selectedPlan->getIncludedPlanningProduct();

            if ($includedPlanningProduct) {
                $remainingQuantity = $planning->getQuantityMax() - $includedPlanningProduct->getQuantity();
            }
        }

        return max(
            0,
            min(
                $remainingQuantity,
                $planning->getQuantityMax(),
                $planning->getAvailability() + $this->getPreviousOrderedQuantity($sheet, $planning)
            )
        );
    }

    /**
     * @param Sheet   $sheet
     * @param Product $product
     *
     * @return float int or INF
     */
    public function getMaxByProduct(Sheet $sheet, Product $product): float
    {
        if ($product->isAttributable()) {
            return min(
                $sheet->countParticipants(),
                $product->getQuantityMax(),
                $product->getAvailability()
            );
        }

        $remainingQuantity = INF;
        $selectedPlan = $this->getSelectedPlan($sheet);

        if ($selectedPlan) {
            $includedProduct = $selectedPlan->getIncludedProduct($product);

            if ($includedProduct) {
                $remainingQuantity = $product->getQuantityMax() - $includedProduct->getQuantity();
            }
        }

        return max(
            0,
            min(
                $remainingQuantity,
                $product->getQuantityMax(),
                $product->getAvailability() + $this->getPreviousOrderedQuantity($sheet, $product)
            )
        );
    }

    private function getSelectedPlan(Sheet $sheet): ?Product
    {
        $cart = $this->cartManager->getCart($sheet);

        if (null !== $cart->getPlanRow()) {
            return $cart->getPlanRow()->getProduct();
        }

        $orderMerged = $this->getMergedOrder($sheet);

        if ($orderMerged instanceof Order) {
            return $orderMerged->getPlan();
        }

        return null;
    }

    private function getMergedOrder(Sheet $sheet): ?Order
    {
        if (isset($this->cachedMergedOrderBySheetId[$sheet->getId()])) {
            return $this->cachedMergedOrderBySheetId[$sheet->getId()];
        }

        if ($sheet->hasNotCancelledOrders()) {
            $order = $this->merger->merge($sheet->getNotCancelledOrders());
            $this->cachedMergedOrderBySheetId[$sheet->getId()] = $order;

            return $order;
        }

        return null;
    }

    private function getPreviousOrderedQuantity(Sheet $sheet, Product $product): int
    {
        $orderMerged = $this->getMergedOrder($sheet);

        if (!$orderMerged instanceof Order) {
            return 0;
        }

        $row = $orderMerged->getRowForProduct($product);

        if (!$row instanceof Order\Row) {
            return 0;
        }

        return $row->getQuantity();
    }
}
