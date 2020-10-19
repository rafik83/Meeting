<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\Sheet\TagFilterAggregator;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\View\Catalog\FilteredFieldsView;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\Catalog\CategoryView;

class FilteredFieldsQueryHandler
{
    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /** @var TagFilterAggregator */
    private $tagFilterAggregator;

    public function __construct(
        SheetSearchAdapterInterface $sheetSearchAdapter,
        TagFilterAggregator $tagFilterAggregator
    ) {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
        $this->tagFilterAggregator = $tagFilterAggregator;
    }

    public function handle(FilteredFieldsQuery $filteredFieldsQuery): FilteredFieldsView
    {
        $this->filterTypeViews($filteredFieldsQuery);
        $this->filterCategoryViews($filteredFieldsQuery);
        $this->filterOrganizationCategoryViews($filteredFieldsQuery);
        $this->filterPositionViews($filteredFieldsQuery);
        $this->filterTaggedNomenclatureTagViews($filteredFieldsQuery);

        return new FilteredFieldsView(
            $filteredFieldsQuery->catalogFilterViewsResult
        );
    }

    private function getTypeAggregation(
        Event $event,
        string $locale,
        array $filters,
        array $typeViews,
        array $availableSlotIds = [],
        array $sheetsToExclude = [],
        ?array $prefilteredSheetIds = null
    ): ?array {
        if (isset($filters[SearchFields::FILTER_TYPE])
            && \count($filters[SearchFields::FILTER_TYPE]) === \count($typeViews)
        ) {
            return null;
        }

        // if type filter is used, type aggs need to be done with a ES query without type filter
        return $this->sheetSearchAdapter->getTypeAggregations(
            $event,
            $locale,
            $filters,
            SearchFields::FILTER_TYPE,
            [],
            $availableSlotIds,
            $sheetsToExclude,
            $prefilteredSheetIds
        );
    }

    /**
     * @param CategoryView[] $categoryViews
     */
    private function getCategoryAggregation(
        Event $event,
        string $locale,
        array $filters,
        array $categoryViews,
        array $availableSlotIds = [],
        array $sheetsToExclude = [],
        ?array $prefilteredSheetIds = null
    ): ?array {
        if (isset($filters[SearchFields::FILTER_CATEGORY])
            && \count($filters[SearchFields::FILTER_CATEGORY]) === \count($categoryViews)
        ) {
            return null;
        }

        // if category filter is used, category aggs need to be done with a ES query category type filter
        return $this->sheetSearchAdapter->getCategoryAggregations(
            $event,
            $locale,
            $filters,
            SearchFields::FILTER_CATEGORY,
            [],
            $availableSlotIds,
            $sheetsToExclude,
            $prefilteredSheetIds
        );
    }

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     *
     * @return array|null
     */
    private function getOrganizationCategoryAggregation(Event $event, string $locale, array $filters, ?array $prefilteredSheetIds): ?array
    {
        if (!isset($filters[SearchFields::FILTER_ORGANIZATION_CATEGORY])) {
            return null;
        }

        // if organizationCategory filter is used,
        // organizationCategory aggs need to be done with a ES query without organizationCategory filter
        return $this->sheetSearchAdapter->getOrganizationCategoryAggregations(
            $event,
            $locale,
            $filters,
            SearchFields::FILTER_ORGANIZATION_CATEGORY,
            $prefilteredSheetIds
        );
    }

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     *
     * @return array|null
     */
    private function getPositionAggregation(Event $event, string $locale, array $filters, ?array $prefilteredSheetIds): ?array
    {
        if (!isset($filters[SearchFields::FILTER_POSITION])) {
            return null;
        }

        // if position filter is used,
        // position aggs need to be done with a ES query without position filter
        return $this->sheetSearchAdapter->getPositionAggregations(
            $event,
            $locale,
            $filters,
            SearchFields::FILTER_POSITION,
            $prefilteredSheetIds
        );
    }

    /**
     * @param FilteredFieldsQuery $filteredFieldsQuery
     */
    private function filterTypeViews(FilteredFieldsQuery $filteredFieldsQuery)
    {
        $typeAggregations = $this->getTypeAggregation(
            $filteredFieldsQuery->event,
            $filteredFieldsQuery->locale,
            $filteredFieldsQuery->filters,
            $filteredFieldsQuery->catalogFilterViewsResult->typeViews,
            $filteredFieldsQuery->availableSlotIds,
            $filteredFieldsQuery->sheetsToExclude,
            $filteredFieldsQuery->prefilteredSheetIds
        );

        $aggregations = null !== $typeAggregations ? $typeAggregations : $filteredFieldsQuery->currentAggregations;

        $aggregationsIndexedByKey = $this->getAggregationsIndexedByKey(
            $aggregations,
            SheetSearchAdapterInterface::ES_FIELD_TYPE
        );

        foreach ($filteredFieldsQuery->catalogFilterViewsResult->typeViews as $typeView) {
            if (isset($aggregationsIndexedByKey[$typeView->id])) {
                $typeView->count = $aggregationsIndexedByKey[$typeView->id];
            }
        }
    }

    /**
     * @param FilteredFieldsQuery $filteredFieldsQuery
     */
    private function filterCategoryViews(FilteredFieldsQuery $filteredFieldsQuery)
    {
        $categoryAggregations = $this->getCategoryAggregation(
            $filteredFieldsQuery->event,
            $filteredFieldsQuery->locale,
            $filteredFieldsQuery->filters,
            $filteredFieldsQuery->catalogFilterViewsResult->categoryViews,
            $filteredFieldsQuery->availableSlotIds,
            $filteredFieldsQuery->sheetsToExclude,
            $filteredFieldsQuery->prefilteredSheetIds
        );

        $aggregations = null !== $categoryAggregations ?
            $categoryAggregations
            : $filteredFieldsQuery->currentAggregations;

        $aggregationsIndexedByKey = $this->getAggregationsIndexedByKey(
            $aggregations,
            SheetSearchAdapterInterface::ES_FIELD_CATEGORIES,
            true
        );

        foreach ($filteredFieldsQuery->catalogFilterViewsResult->categoryViews as $categoryView) {
            if (isset($aggregationsIndexedByKey[$categoryView->id])) {
                $categoryView->count = $aggregationsIndexedByKey[$categoryView->id];
            }
        }
    }

    /**
     * @param FilteredFieldsQuery $filteredFieldsQuery
     */
    private function filterOrganizationCategoryViews(FilteredFieldsQuery $filteredFieldsQuery)
    {
        $aggregations = $this->getOrganizationCategoryAggregation(
            $filteredFieldsQuery->event,
            $filteredFieldsQuery->locale,
            $filteredFieldsQuery->filters,
            $filteredFieldsQuery->prefilteredSheetIds
        );

        $aggregations = $aggregations ?? $filteredFieldsQuery->currentAggregations;

        $aggregationsIndexedByKey = $this->getAggregationsIndexedByKey(
            $aggregations,
            SheetSearchAdapterInterface::ES_FIELD_ORGANIZATION_CATEGORY
        );

        if (empty($aggregationsIndexedByKey)) {
            $filteredFieldsQuery->catalogFilterViewsResult->organizationCategoryViews = [];

            return;
        }

        foreach ($filteredFieldsQuery->catalogFilterViewsResult->organizationCategoryViews as $index => $organizationCategoryView) {
            // Show only filter which have result
            if (!isset($aggregationsIndexedByKey[$organizationCategoryView->key])
                || 0 === $aggregationsIndexedByKey[$organizationCategoryView->key]
            ) {
                unset($filteredFieldsQuery->catalogFilterViewsResult->organizationCategoryViews[$index]);
            }
        }
    }

    /**
     * @param FilteredFieldsQuery $filteredFieldsQuery
     */
    private function filterPositionViews(FilteredFieldsQuery $filteredFieldsQuery)
    {
        $aggregations = $this->getPositionAggregation(
            $filteredFieldsQuery->event,
            $filteredFieldsQuery->locale,
            $filteredFieldsQuery->filters,
            $filteredFieldsQuery->prefilteredSheetIds
        );

        $aggregations = $aggregations ?? $filteredFieldsQuery->currentAggregations;

        $aggregationsIndexedByKey = $this->getAggregationsIndexedByKey(
            $aggregations,
            SheetSearchAdapterInterface::ES_FIELD_POSITION,
            true
        );

        if (empty($aggregationsIndexedByKey)) {
            $filteredFieldsQuery->catalogFilterViewsResult->positionViews = [];

            return;
        }

        foreach ($filteredFieldsQuery->catalogFilterViewsResult->positionViews as $index => $positionView) {
            // Show only filter which have result
            if (!isset($aggregationsIndexedByKey[$positionView->getKey()])
                || 0 === $aggregationsIndexedByKey[$positionView->getKey()]
            ) {
                unset($filteredFieldsQuery->catalogFilterViewsResult->positionViews[$index]);
            }
        }
    }

    /**
     * @param array  $aggregations ElasticSearch aggregations
     * @param string $fieldName    ElasticSearch field name
     * @param bool   $subField:    is aggregations is organized in subfield: $aggregations['position']['position'] = [...]
     *                             else: $aggregations['type'] = [...]
     *
     * @return array
     */
    private function getAggregationsIndexedByKey(array $aggregations, string $fieldName, bool $subField = false): array
    {
        if (null === $aggregations || !isset($aggregations[$fieldName])) {
            return [];
        }

        if (!$subField && !isset($aggregations[$fieldName][SheetSearchAdapterInterface::ES_BUCKETS])) {
            return [];
        }

        if ($subField && (
            !isset($aggregations[$fieldName][$fieldName])
            || !isset($aggregations[$fieldName][$fieldName][SheetSearchAdapterInterface::ES_BUCKETS])
        )) {
            return [];
        }

        if ($subField) {
            $items = $aggregations[$fieldName][$fieldName][SheetSearchAdapterInterface::ES_BUCKETS];
        } else {
            $items = $aggregations[$fieldName][SheetSearchAdapterInterface::ES_BUCKETS];
        }

        $aggregationsIndexedByKey = [];

        foreach ($items as $item) {
            $aggregationsIndexedByKey[$item[SheetSearchAdapterInterface::ES_KEY]] = $item[SheetSearchAdapterInterface::ES_DOC_COUNT];
        }

        return $aggregationsIndexedByKey;
    }

    private function filterTaggedNomenclatureTagViews(FilteredFieldsQuery $filteredFieldsQuery): void
    {
        foreach ($filteredFieldsQuery->catalogFilterViewsResult->taggedNomenclatureTagViews as $nomenclatureTagViews) {
            if ($nomenclatureTagViews->maxDepth > 1) {
                continue;
            }

            $tag = $nomenclatureTagViews->tag;

            $aggregations = $this->tagFilterAggregator->getAggregationsForTag(
                $filteredFieldsQuery->event,
                $tag,
                $filteredFieldsQuery->locale,
                $filteredFieldsQuery->filters,
                $filteredFieldsQuery->availableSlotIds,
                $filteredFieldsQuery->sheetsToExclude,
                $filteredFieldsQuery->prefilteredSheetIds
            );

            $aggregationKeys = [];

            foreach ($aggregations as $aggregation) {
                $key = $aggregation['key'] ?? null;

                if (null === $key) {
                    continue;
                }

                $aggregationKeys[$key] = $key;
            }

            foreach ($nomenclatureTagViews->nomenclatureTagViews as $key => $nomenclatureTagView) {
                if (!isset($aggregationKeys[$nomenclatureTagView->key])) {
                    unset($nomenclatureTagViews->nomenclatureTagViews[$key]);
                }
            }
        }
    }
}
