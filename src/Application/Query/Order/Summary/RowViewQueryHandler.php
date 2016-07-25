<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\CustomRowView;
use Proximum\Vimeet\Application\View\Order\IncludedProductView;
use Proximum\Vimeet\Application\View\Order\RowView;
use Proximum\Vimeet\Domain\Order\Row\ProductIncludedInfoGuesser;

class ProductViewQueryHandler
{
    /**
     * @var ProductIncludedInfoGuesser
     */
    private $productIncludedInfoGuesser;

    /**
     * @param ProductIncludedInfoGuesser $productIncludedInfoGuesser
     */
    public function __construct(ProductIncludedInfoGuesser $productIncludedInfoGuesser)
    {
        $this->productIncludedInfoGuesser = $productIncludedInfoGuesser;
    }

    /**
     * @param ProductViewQuery $productViewQuery
     *
     * @return RowView
     */
    public function handle(ProductViewQuery $productViewQuery)
    {
        $rowView = new RowView(
            $productViewQuery->row->getId(),
            $productViewQuery->row->getProductId(),
            $productViewQuery->row->getLabel($productViewQuery->locale),
            $productViewQuery->row->getPrice(),
            $productViewQuery->row->getQuantity(),
            $productViewQuery->order->getVatMode(),
            $productViewQuery->order->getCurrency()
        );

        foreach ($productViewQuery->order->getCustomRowForProduct($productViewQuery->row) as $customRow) {
            $rowView->addCustomRow(new CustomRowView(
                $customRow->getId(),
                $customRow->getLabel(),
                $customRow->getPrice(),
                $customRow->getQuantity()
            ));
        }

        if ($productViewQuery->row->hasIncludedProduct()) {
            $includedProducts = $this->productIncludedInfoGuesser->getProductIncludedInfo(
                $productViewQuery->row,
                $productViewQuery->locale
            );

            foreach ($includedProducts as $includedProduct) {
                $rowView->addIncludedProduct(
                    new IncludedProductView(
                        $includedProduct['id'],
                        $includedProduct['label'],
                        $includedProduct['price'],
                        $includedProduct['quantity'],
                        $productViewQuery->order->getVatMode(),
                        $productViewQuery->order->getCurrency()
                    )
                );
            }
        }

        if (null !== $productViewQuery->planView) {
            foreach ($productViewQuery->planView->includedProducts as $key => $includedView) {
                if (null !== $includedView && $includedView->id === $rowView->productId) {
                    $rowView->addIncludedProduct($includedView);
                    unset($productViewQuery->planView->includedProducts[$key]);
                }
            }
        }

        return $rowView;
    }
}
