<?php

namespace Proximum\Vimeet\Application\Query\Order\OrderVat;

use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Application\View\Package\Vat\VatView;
use Proximum\Vimeet\Domain\Money\AmountFormatter;

class VatListViewQueryHandler
{
    public function handle(VatListViewQuery $query): VatListView
    {
        $order = $query->order;
        $totalWithoutVat = AmountFormatter::decimalToCentsAmount($order->getTotalWithoutVat());
        $vatAmount = 0;

        $vatViews = [];

        if (true === $query->isVatApplicable) {
            foreach ($order->getRows() as $row) {
                $this->addToVatViews(
                    $vatViews,
                    $row->getVatRate(),
                    AmountFormatter::decimalToCentsAmount($row->getPrice()) * $row->getQuantity(),
                    $order->getVatMode()
                );
            }

            foreach ($order->getPromotionCodes() as $promotionCodeRow) {
                $this->addToVatViews(
                    $vatViews,
                    $promotionCodeRow->getVatRate(),
                    AmountFormatter::decimalToCentsAmount($promotionCodeRow->getPrice()),
                    $order->getVatMode()
                );
            }

            foreach ($vatViews as $vatView) {
                $vatAmount += $vatView->totalVat;
            }
        }

        $totalWithVat = $vatAmount + $totalWithoutVat;

        return new VatListView(
            $totalWithoutVat,
            $totalWithVat,
            $query->isVatApplicable,
            $order->getVatMode(),
            $vatViews
        );
    }

    /**
     * @param array  $vatViews
     * @param float  $vatRate
     * @param int    $price    in cents
     * @param string $vatMode
     */
    private function addToVatViews(array &$vatViews, float $vatRate, int $price, string $vatMode): void
    {
        $index = 'vat_' . $vatRate;

        if (!isset($vatViews[$index])) {
            $vatViews[$index] = new VatView($vatRate, $vatMode, 0, 0);
        }

        $vatViews[$index]->addToTotal($price);
    }
}
