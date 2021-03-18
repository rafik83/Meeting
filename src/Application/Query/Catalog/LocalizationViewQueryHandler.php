<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\View\Catalog\LocalizationView;

class LocalizationViewQueryHandler
{
    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    public function __construct(SheetSearchAdapterInterface $sheetSearchAdapter)
    {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    /**
     * @param LocalizationViewQuery $query
     *
     * @return LocalizationView[]
     */
    public function handle(LocalizationViewQuery $query): array
    {
        $localizations = $this->sheetSearchAdapter->findLocalization(
            $query->event,
            $query->filter,
            $query->defaultFilters,
            $query->locale
        );

        $localizationViews = [];

        // handle city
        if (!empty($localizations['cities_aggs']['cities'])) {
            foreach ($localizations['cities_aggs']['cities']['buckets'] as $city) {
                $localizationViews[] = new LocalizationView($city['key']);
            }
        }

        // handle country
        if (!empty($localizations['countries_aggs']['countries']['countries_filter']['countries'])) {
            foreach ($localizations['countries_aggs']['countries']['countries_filter']['countries']['buckets'] as $country) {
                $localizationViews[] = new LocalizationView($country['key']);
            }
        }

        return $localizationViews;
    }
}
