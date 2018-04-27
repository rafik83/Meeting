<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Option;

use Proximum\Vimeet\Application\View\Package\ProductView;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Order\Merger;

class OptionViewQueryHandler
{
    /**
     * @var CartManager
     */
    private $cartManager;
    /**
     * @var \DateTimeInterface
     */
    private $now;

    /**
     * @var Merger
     */
    private $merger;

    /**
     * @param CartManager        $cartManager
     * @param Merger             $merger
     * @param \DateTimeInterface $now
     */
    public function __construct(
        CartManager $cartManager,
        Merger $merger,
        \DateTimeInterface $now
    ) {
        $this->cartManager = $cartManager;
        $this->now         = $now;
        $this->merger      = $merger;
    }

    /**
     * @param OptionViewQuery $optionViewQuery
     *
     * @return ProductView
     */
    public function handle(OptionViewQuery $optionViewQuery)
    {
        $cart         = $this->cartManager->getCart($optionViewQuery->sheet);
        $selectedPlan = null;
        $included     = 0;

        if (null !== $cart->getPlanRow()) {
            $selectedPlan = $cart->getPlanRow()->getProduct();
        }

        // handle new order
        if ($optionViewQuery->sheet->hasNotCancelledOrders()) {
            $orderMerged  = $this->merger->merge($optionViewQuery->sheet->getNotCancelledOrders());
            $selectedPlan = $orderMerged->getPlan();
        }

        if ($selectedPlan) {
            $includedProduct = $selectedPlan->getIncludedProduct($optionViewQuery->product);

            if ($includedProduct) {
                $included = $includedProduct->getQuantity();
            }
        }

        return new ProductView(
            $optionViewQuery->product->getId(),
            $optionViewQuery->product->getTitle($optionViewQuery->locale),
            $optionViewQuery->product->getUnitPrice(),
            $optionViewQuery->product->getHeading($optionViewQuery->locale),
            $optionViewQuery->product->getDescription($optionViewQuery->locale),
            $optionViewQuery->product->getAddon($optionViewQuery->locale),
            $optionViewQuery->product->getImage(),
            $optionViewQuery->product->getAvailabilityCurrent(),
            $optionViewQuery->product->getAvailabilityMax(),
            $optionViewQuery->product->isOutOfStock(),
            $optionViewQuery->sheet->getEvent()->getMode(),
            $optionViewQuery->sheet->getEvent()->getCurrency(),
            $optionViewQuery->product->getSubjectedToValidationHelp($optionViewQuery->locale),
            $optionViewQuery->product->isSubjectedToValidation(),
            $included,
            $optionViewQuery->product->isBuyable($this->now)
        );
    }
}
