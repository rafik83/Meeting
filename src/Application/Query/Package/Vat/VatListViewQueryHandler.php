<?php

namespace Proximum\Vimeet\Application\Query\Package\Vat;

use Proximum\Vimeet\Application\View\Package\Summary\ProductView;
use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Application\View\Package\Vat\VatView;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;

class VatListViewQueryHandler
{
    /** @var VatApplicable */
    private $vatApplicable;

    /**
     * @param VatApplicable $vatApplicable
     */
    public function __construct(
        VatApplicable $vatApplicable
    ) {
        $this->vatApplicable = $vatApplicable;
    }

    /**
     * @param VatListViewQuery $query
     *
     * @throws MissingBillingInfoException
     *
     * @return VatListView
     */
    public function handle(VatListViewQuery $query): VatListView
    {
        $vatApplicable = $this->vatApplicable->onSheet($query->sheet);

        $total = AmountFormatter::decimalToCentsAmount($query->groups->getTotal() + $query->promotionCodes->getTotal());
        $totalWithVat = $total;
        $vatViews = [];

        if (true === $vatApplicable) {
            $planGroup = $query->groups->planGroup;
            if (null !== $planGroup) {
                foreach ($planGroup->options as $option) {
                    $this->handleVatViews($vatViews, $option);
                }
            }

            $participantAndPlanningGroup = $query->groups->participantAndPlanningGroup;
            if (null !== $participantAndPlanningGroup) {
                foreach ($participantAndPlanningGroup->options as $option) {
                    $this->handleVatViews($vatViews, $option);
                }
            }

            foreach ($query->groups->groups as $group) {
                foreach ($group->options as $option) {
                    $this->handleVatViews($vatViews, $option);
                }
            }

            foreach ($query->promotionCodes->promotionCodes as $promotionCode) {
                foreach ($promotionCode->promotionProductRowViews as $promotionProductRowView) {
                    $index = 'vat_' . (string) $promotionProductRowView->vatRate;

                    if (!isset($vatViews[$index])) {
                        $vatViews[$index] = new VatView($promotionProductRowView->vatRate, $promotionCode->vatMode, 0, 0);
                    }

                    /** @var VatView $vatView */
                    $vatView = $vatViews[$index];
                    $discount = $promotionProductRowView->totalDiscount;
                    $vatView->addToTotal(AmountFormatter::decimalToCentsAmount($discount));
                }
            }
        }

        foreach ($vatViews as $vatView) {
            $totalWithVat += $vatView->totalVat;
        }

        $vatListView = new VatListView(
            $total,
            $totalWithVat,
            $vatApplicable,
            $query->sheet->getEvent()->getMode(),
            $vatViews
        );

        return $vatListView;
    }

    /**
     * @param array       $vatViews
     * @param ProductView $productView
     */
    public function handleVatViews(array &$vatViews, ProductView $productView): void
    {
        $index = 'vat_' . (string) $productView->vatRate;

        if (!isset($vatViews[$index])) {
            $vatViews[$index] = new VatView($productView->vatRate, $productView->vatMode, 0, 0);
        }

        /** @var VatView $vatView */
        $vatView = $vatViews[$index];

        $vatView->addToTotal(AmountFormatter::decimalToCentsAmount($productView->total));
    }
}
