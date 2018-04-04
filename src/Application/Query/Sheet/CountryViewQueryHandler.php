<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Adapter\IntlInterface;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\View\Sheet\CountryView;

class CountryViewQueryHandler
{
    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /** @var IntlInterface */
    private $intl;

    public function __construct(SheetSearchAdapterInterface $sheetSearchAdapter, IntlInterface $intl)
    {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
        $this->intl = $intl;
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
        $countries     = $localizations['countryCodes'] ?? [];

        if (!empty($countries)) {
            foreach ($countries['buckets'] as $country) {
                $countryCode = $country['key'];
                $countryName = $this->intl->getCountryName($countryCode, $query->locale);

                if (null !== $countryName) {
                    $countryViews[] = new CountryView($countryName, $countryCode);
                }
            }
        }

        usort($countryViews, function (CountryView $one, CountryView $other) {
                return strcasecmp($one->name, $other->name);
            }
        );

        return $countryViews;
    }
}
