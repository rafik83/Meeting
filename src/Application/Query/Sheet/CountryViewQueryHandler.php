<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\View\Sheet\CountryView;

class CountryViewQueryHandler
{
    /**
     * @var SheetSearchAdapterInterface
     */
    private $sheetSearchAdapter;

    public function __construct(SheetSearchAdapterInterface $sheetSearchAdapter)
    {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    /**
     * @param CountryViewQuery $query
     *
     * @return CountryView[]
     */
    public function handle(CountryViewQuery $query): array
    {
        $countryViews  = [];
        $localizations = $this->sheetSearchAdapter->getCountries($query->event, $query->locale);
        $countries     = $localizations['countries_aggs']['countries']['countries_filter']['countries'] ?? [];

        if (!empty($countries)) {
            foreach ($countries['buckets'] as $country) {
                $countryViews[] = new CountryView($country['key']);
            }
        }

        return $countryViews;
    }
}
