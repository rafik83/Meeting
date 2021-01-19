<?php

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\IncludedView;
use Proximum\Vimeet\Application\View\Package\Summary\ProductView;

class ProductViewQueryHandler
{
    /**
     * @param ProductViewQuery $productViewQuery
     *
     * @throws \Exception
     *
     * @return ProductView
     */
    public function handle(ProductViewQuery $productViewQuery)
    {
        $cart    = $productViewQuery->cart;
        $cartRow = $cart->getCartRowForProduct($productViewQuery->product);

        if (null === $cartRow) {
            throw new \Exception('Product not found');
        }

        $productView = new ProductView(
            $productViewQuery->product->getId(),
            $productViewQuery->product->getTitle($productViewQuery->locale),
            $productViewQuery->product->getUnitPrice(),
            $cartRow->getQuantity(), // quantity
            $productViewQuery->product->getUnitPrice() * $cartRow->getQuantity(), // total
            $productViewQuery->sheet->getEvent()->getMode(),
            $productViewQuery->product->getVat(),
            $productViewQuery->sheet->getEvent()->getCurrency()
        );

        if ($productViewQuery->product->isPlan()) {
            foreach ($productViewQuery->product->getIncludedProducts() as $includedProduct) {
                $productView->addIncludedProduct(new IncludedView(
                    $includedProduct->getIncluded()->getId(),
                    $includedProduct->getIncluded()->getTitle($productViewQuery->locale),
                    $includedProduct->getQuantity(),
                    $productViewQuery->sheet->getEvent()->getMode(),
                    $productViewQuery->sheet->getEvent()->getCurrency()
                ));
            }
        } elseif (!$productViewQuery->product->isPlan()
            && null !== $productViewQuery->planGroupView
            && !empty($productViewQuery->planGroupView->options)
        ) {
            /**
             * @var ProductView
             */
            $planView = reset($productViewQuery->planGroupView->options);

            if (null !== $planView && !empty($planView->included)) {
                foreach ($planView->included as $key => $includedView) {
                    if (null !== $includedView && $includedView->id === $productView->id) {
                        $productView->addIncludedProduct($includedView);
                        unset($planView->included[$key]);
                    }
                }
            }
        }

        return $productView;
    }
}
