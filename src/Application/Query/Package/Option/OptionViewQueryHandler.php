<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Option;

use Proximum\Vimeet\Application\View\Package\OptionView;

class OptionViewQueryHandler
{
    /**
     * @param OptionViewQuery $optionViewQuery
     *
     * @return OptionView
     */
    public function handle(OptionViewQuery $optionViewQuery)
    {
        return new OptionView(
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
            $optionViewQuery->sheet->getEvent()->getCurrency()
        );
    }
}
