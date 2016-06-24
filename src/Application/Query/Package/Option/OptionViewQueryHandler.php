<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Option;

use Proximum\Vimeet\Application\View\Package\ProductView;
use Proximum\Vimeet\Domain\Cart\CartManager;

class OptionViewQueryHandler
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @param CartManager $cartManager
     */
    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * @param OptionViewQuery $optionViewQuery
     *
     * @return ProductView
     */
    public function handle(OptionViewQuery $optionViewQuery)
    {
        $cart         = $this->cartManager->getCart($optionViewQuery->sheet);
        $selectedPlan = $cart->getPlanRow();
        $included     = 0;

        if ($selectedPlan) {
            $includedProduct = $selectedPlan->getProduct()->getIncludedProduct($optionViewQuery->product);

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
            $included
        );
    }
}
