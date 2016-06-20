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

class OptionViewQueryHandler
{
    /**
     * @param OptionViewQuery $optionViewQuery
     *
     * @return ProductView
     */
    public function handle(OptionViewQuery $optionViewQuery)
    {
        $included = 0;

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
            $included
        );
    }
}
