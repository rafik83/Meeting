<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\View\Catalog\LocalizationView;

class LocalizationViewQueryHandler
{
    /**
     * @var SheetSearchAdapterInterface
     */
    private $sheetSearchAdapter;

    /**
     * LocalizationViewQueryHandler constructor.
     *
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     */
    public function __construct(SheetSearchAdapterInterface $sheetSearchAdapter)
    {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    /**
     * @param LocalizationViewQuery $query
     *
     * @return LocalizationView[]
     */
    public function handle(LocalizationViewQuery $query)
    {
        $localizations = $this->sheetSearchAdapter->findLocalization(
            $query->event,
            $query->filter,
            $query->locale
        );

        $localizationViews = [];

        if (!empty($localizations['cities_aggs']['cities'])) {
            foreach ($localizations['cities_aggs']['cities']['buckets'] as $city) {
                $localizationViews[] = new LocalizationView($city['key']);
            }
        }

        if (!empty($localizations['zipcode_aggs']['zipcodes'])) {
            foreach ($localizations['zipcode_aggs']['zipcodes']['buckets'] as $zipcode) {
                $localizationViews[] = new LocalizationView($zipcode['key']);
            }
        }

        return $localizationViews;
    }
}
