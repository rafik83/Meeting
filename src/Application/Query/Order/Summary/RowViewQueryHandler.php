<?php

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\IncludedProductView;
use Proximum\Vimeet\Application\View\Order\RowView;
use Proximum\Vimeet\Domain\Order\Row\ProductIncludedInfoGuesser;

class RowViewQueryHandler
{
    /**
     * @var ProductIncludedInfoGuesser
     */
    private $productIncludedInfoGuesser;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var CustomRowViewQueryHandler
     */
    private $customRowViewQueryHandler;

    /**
     * @param ProductIncludedInfoGuesser $productIncludedInfoGuesser
     * @param CustomRowViewQueryHandler  $customRowViewQueryHandler
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        ProductIncludedInfoGuesser $productIncludedInfoGuesser,
        CustomRowViewQueryHandler $customRowViewQueryHandler,
        \DateTimeInterface $dateTime
    ) {
        $this->productIncludedInfoGuesser = $productIncludedInfoGuesser;
        $this->customRowViewQueryHandler  = $customRowViewQueryHandler;
        $this->dateTime                   = $dateTime;
    }

    /**
     * @param RowViewQuery $rowViewQuery
     *
     * @return RowView
     */
    public function handle(RowViewQuery $rowViewQuery)
    {
        $rowView = new RowView(
            $rowViewQuery->row->getId(),
            $rowViewQuery->row->getProductId(),
            $rowViewQuery->row->getLabel($rowViewQuery->locale),
            $rowViewQuery->row->getPrice(),
            $rowViewQuery->row->getQuantity(),
            $rowViewQuery->order->getVatMode(),
            $rowViewQuery->row->getVatRate(),
            $rowViewQuery->order->getCurrency(),
            $rowViewQuery->row->getProduct()->getBuyableUntil(),
            $rowViewQuery->row->getProduct()->getDeletableUntil(),
            $rowViewQuery->row->getProduct()->isBuyable($this->dateTime),
            $rowViewQuery->row->getProduct()->isDeletable($this->dateTime)
        );

        foreach ($rowViewQuery->order->getCustomRowsForProduct($rowViewQuery->row) as $customRow) {
            $rowView->addCustomRow(
                $this->customRowViewQueryHandler->handle(
                    new CustomRowViewQuery($customRow, $rowViewQuery->locale)
                )
            );
        }

        if ($rowViewQuery->row->hasIncludedProduct()) {
            $includedProducts = $this->productIncludedInfoGuesser->getProductIncludedInfo(
                $rowViewQuery->row,
                $rowViewQuery->locale
            );

            foreach ($includedProducts as $includedProduct) {
                $rowView->addIncludedProduct(
                    new IncludedProductView(
                        $includedProduct['id'],
                        $includedProduct['label'],
                        $includedProduct['price'],
                        $includedProduct['quantity'],
                        $rowViewQuery->order->getVatMode(),
                        $rowViewQuery->order->getCurrency(),
                        $rowViewQuery->row->getProduct()->getBuyableUntil(),
                        $rowViewQuery->row->getProduct()->getDeletableUntil(),
                        $rowViewQuery->row->getProduct()->isBuyable($this->dateTime),
                        $rowViewQuery->row->getProduct()->isDeletable($this->dateTime)
                    )
                );
            }
        }

        if (null !== $rowViewQuery->planView) {
            foreach ($rowViewQuery->planView->includedProducts as $key => $includedView) {
                if (null !== $includedView && $includedView->id === $rowView->productId) {
                    $rowView->addIncludedProduct($includedView);
                    unset($rowViewQuery->planView->includedProducts[$key]);
                }
            }
        }

        return $rowView;
    }
}
