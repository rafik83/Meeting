<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\ProductView;
use Proximum\Vimeet\Domain\Order\Row\ProductIncludedInfoGuesser;

class ProductViewQueryHandler
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
     * @param ProductIncludedInfoGuesser $productIncludedInfoGuesser
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        ProductIncludedInfoGuesser $productIncludedInfoGuesser,
        \DateTimeInterface $dateTime
    ) {
        $this->productIncludedInfoGuesser = $productIncludedInfoGuesser;
        $this->dateTime                   = $dateTime;
    }

    /**
     * @param ProductViewQuery $productViewQuery
     *
     * @return ProductView
     */
    public function handle(ProductViewQuery $productViewQuery)
    {
        $productView = new ProductView(
            $productViewQuery->row->getProductId(),
            $productViewQuery->row->getLabel($productViewQuery->locale),
            $productViewQuery->row->getPrice(),
            $productViewQuery->row->getQuantity(),
            $productViewQuery->order->getVatMode(),
            $productViewQuery->order->getCurrency(),
            $productViewQuery->row->getProduct()->getBuyableUntil(),
            $productViewQuery->row->getProduct()->getDeletableUntil(),
            $productViewQuery->row->getProduct()->isBuyable($this->dateTime),
            $productViewQuery->row->getProduct()->isDeletable($this->dateTime)
        );

        if ($productViewQuery->row->hasIncludedProduct()) {
            $infos = $this
                ->productIncludedInfoGuesser
                ->getProductIncludedInfo(
                    $productViewQuery->row,
                    $productViewQuery->locale
                );

            foreach ($infos as $info) {
                $productView->addIncludedProduct(
                    new ProductView(
                        $info['id'],
                        $info['label'],
                        $info['price'],
                        $info['quantity'],
                        $productViewQuery->order->getVatMode(),
                        $productViewQuery->order->getCurrency(),
                        $productViewQuery->row->getProduct()->getBuyableUntil(),
                        $productViewQuery->row->getProduct()->getDeletableUntil(),
                        $productViewQuery->row->getProduct()->isBuyable($this->dateTime),
                        $productViewQuery->row->getProduct()->isDeletable($this->dateTime)
                    )
                );
            }
        }

        if (null !== $productViewQuery->planView) {
            foreach ($productViewQuery->planView->includedProducts as $key => $includedView) {
                if (null !== $includedView && $includedView->id === $productView->id) {
                    $productView->addIncludedProduct($includedView);
                    unset($productViewQuery->planView->includedProducts[$key]);
                }
            }
        }

        return $productView;
    }
}
