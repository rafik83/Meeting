<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Terms;
use Elastica\Query;
use Elastica\Query\FunctionScore;
use Elastica\Result;
use Elastica\SearchableInterface;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\ElasticSearchConstant;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\SheetListView;
use Proximum\Vimeet\Application\View\Participant\ParticipantsSheetIdsView;
use Proximum\Vimeet\Application\View\Sheet\SheetIdsView;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\ConditionRulesTransformerInterface;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ForeignChar;

class SheetSearchAdapter implements SheetSearchAdapterInterface
{
    public const NOMENCLATURE_ITEMS_WEIGHT = 1.1;

    /** @var PaginatedFinderInterface Elastica finder */
    private $finder;

    /** @var SearchableInterface */
    private $searchable;

    /** @var ConditionRulesTransformerInterface */
    private $conditionRulesTransformer;

    public function __construct(
        PaginatedFinderInterface $finder,
        SearchableInterface $searchable,
        ConditionRulesTransformerInterface $conditionRulesTransformer
    ) {
        $this->finder = $finder;
        $this->searchable = $searchable;
        $this->conditionRulesTransformer = $conditionRulesTransformer;
    }

    /**
     * @param Event       $event
     * @param array       $filters
     * @param string|null $orderBy
     * @param string      $locale
     * @param array       $nomenclatureItems
     * @param array       $availableSlotIds
     * @param array       $sheetsToExclude
     *
     * @return Query
     */
    private function getQueryToFind(
        Event $event,
        array $filters,
        string $orderBy = null,
        string $locale,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): Query {
        $nomenclatureBoost = isset($nomenclatureItems[Nomenclature::OBJECTIVE_NONE])
            ? \count($nomenclatureItems[Nomenclature::OBJECTIVE_NONE])
            : 1;

        $builder = new SheetSearchQueryBuilder(
            $event,
            $filters,
            $locale,
            $nomenclatureBoost,
            $nomenclatureItems,
            $availableSlotIds,
            $sheetsToExclude
        );

        if (Constant::ORDER_BY_DATE_ADDED_TO_CATALOG === $orderBy) {
            $query = new Query($builder->getQuery());
            $query->addSort(['inCatalogAt' => 'desc']);
        } elseif (Constant::ORDER_BY_RELEVANCE === $orderBy) {
            $builtQuery = $builder->getQuery();

            if (isset($nomenclatureItems[Nomenclature::OBJECTIVE_NONE])) {
                $functionScore = new FunctionScore();
                $functionScore->setScoreMode(FunctionScore::SCORE_MODE_SUM);

                foreach ($nomenclatureItems[Nomenclature::OBJECTIVE_NONE] as $key) {
                    if (is_array($key)) {
                        foreach ($key as $item) {
                            $this->setKeyToFunctionScore($functionScore, $item);
                        }

                        continue;
                    }

                    $this->setKeyToFunctionScore($functionScore, $key);
                }

                $builtQuery = $functionScore->setQuery($builtQuery);
            }

            $query = new Query($builtQuery);
            $query->addSort(['_score' => 'desc']);
        } elseif (Constant::ORDER_BY_CREATED_AT === $orderBy) {
            $query = new Query($builder->getQuery());
            $query->addSort(['createdAt' => 'desc']);
        } elseif (Constant::ORDER_BY_COMPLETENESS === $orderBy) {
            $query = new Query($builder->getQuery());
            $query->addSort(['completeness' => 'desc']);
        } else {
            $query = new Query($builder->getQuery());
            $query->addSort(['sheetName.raw' => 'asc']);
            $query->setFieldDataFields(['sheetName.raw']);
        }

        return $query;
    }

    private function setKeyToFunctionScore(FunctionScore $functionScore, $key): void
    {
        if (!is_string($key)) {
            return;
        }

        $nested = new \Elastica\Query\Nested();
        $nested->setQuery((new Query\Term())->setTerm('nomenclatureItems.key', $key));
        $nested->setPath('nomenclatureItems');
        $functionScore->addFunction('weight', [], $nested, self::NOMENCLATURE_ITEMS_WEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function find(
        Event $event,
        array $filters,
        string $orderBy = null,
        string $locale,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): array {
        $query = $this->getQueryToFind(
            $event,
            $filters,
            $orderBy,
            $locale,
            $nomenclatureItems,
            $availableSlotIds,
            $sheetsToExclude
        );
        $options = ['size' => ElasticSearchConstant::LONG_RESULTS_NUMBER];

        return $this->searchable->search($query, $options)->getResults();
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(
        Event $event,
        array $filters,
        string $orderBy = null,
        int $page,
        int $limit,
        string $locale,
        bool $getAggregations,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = [],
        ?RuleInterface $condition = null
    ): PaginatedResult {
        $query = $this->getQueryToFind(
            $event,
            $filters,
            $orderBy,
            $locale,
            $nomenclatureItems,
            $availableSlotIds,
            $sheetsToExclude
        );

        if (true === $getAggregations) {
            $query->addAggregation($this->getAggregation(self::ES_FIELD_TYPE));
            $query->addAggregation($this->getAggregation(self::ES_FIELD_ORGANIZATION_CATEGORY));
            $query->addAggregation(
                $this->getNestedAggregation(self::ES_FIELD_CATEGORIES, self::ES_PATH_CATEGORIES)
            );
            $query->addAggregation(
                $this->getNestedAggregation(self::ES_FIELD_POSITION, self::ES_PATH_POSITION)
            );
        }

        $queryToArray = $query->toArray();
        if ($condition instanceof Condition) {
            $rules = $this->conditionRulesTransformer->transform($condition);

            if ($rules) {
                $queryToArray['query']['bool']['must'][] = $rules;
            }
        }

        $query = new Query();
        $query->setRawQuery($queryToArray);

        try {
            $result = $this->finder->findPaginated($query)->setMaxPerPage($limit)->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw new UnavailableCurrentPageException(sprintf('Current page %s not available', $page));
        }

        /** @var PaginatorAdapterInterface $paginatorAdapter */
        $paginatorAdapter = $result->getAdapter();

        return new PaginatedResult(
            $result->getCurrentPageResults(),
            $page,
            $limit,
            $result->getNbResults(),
            true === $getAggregations ? $paginatorAdapter->getAggregations() : null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetIds(Event $event, array $filters, string $locale, ?RuleInterface $condition = null): array
    {
        $builder = new SheetSearchQueryBuilder($event, $filters, $locale);

        $queryToArray = ['query' => $builder->getQuery()->toArray()];
        if ($condition instanceof Condition) {
            $rules = $this->conditionRulesTransformer->transform($condition);

            if ($rules) {
                $queryToArray['query']['bool']['must'][] = $rules;
            }
        }

        $query = new Query();
        $query->setRawQuery($queryToArray);
        $query->setStoredFields(['id']);

        return array_map(static function (Result $sheet) {
            return $sheet->id[0];
        }, $this->searchable->search($query, ['limit' => ElasticSearchConstant::LONG_RESULTS_NUMBER])->getResults());
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetListView(
        Event $event,
        array $filters,
        string $locale,
        ?RuleInterface $condition = null
    ): array {
        $results = $this->getSearchResults($event, $filters, $locale, $condition);

        return array_map(static function (Result $result) {
            return new SheetListView($result->id, $result->sheetName, $result->ownerEmail);
        }, $results);
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetIdsView(Event $event, array $filters, string $locale, ?RuleInterface $condition = null): SheetIdsView
    {
        $results = $this->getSearchResults($event, $filters, $locale, $condition);

        $sheetIds = array_map(static function (Result $result) {
            return $result->id;
        }, $results);

        return new SheetIdsView($sheetIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantsSheetIdsView(Event $event, array $filters, string $locale): ParticipantsSheetIdsView
    {
        $results = $this->getSearchResults($event, $filters, $locale);

        $sheetIds = array_map(static function (Result $result) {
            return $result->id;
        }, $results);

        return new ParticipantsSheetIdsView($sheetIds);
    }

    /**
     * {@inheritdoc}
     */
    public function findLocalization(Event $event, string $filter, array $defaultFilters, string $locale): array
    {
        $builder = new SheetSearchQueryBuilder(
            $event,
            $defaultFilters,
            $locale
        );

        $query = new Query($builder->getQuery());
        $query->addAggregation($this->findCityQuery($event, $filter))
            ->addAggregation($this->findCountryQuery($event, $filter, $locale))
            ->setSize(ElasticSearchConstant::LONG_RESULTS_NUMBER);

        return $this->searchable->search($query)->getAggregations();
    }

    /**
     * {@inheritdoc}
     */
    public function getCountries(Event $event, string $locale): array
    {
        $builder = new SheetSearchQueryBuilder(
            $event,
            [],
            $locale
        );

        $query = new Query($builder->getQuery());

        $countryAggregations = new Terms('countryCodes');
        $countryAggregations->setField('countryCode');
        $countryAggregations->setSize(1000);

        $query->addAggregation($countryAggregations);
        $query->setSize(ElasticSearchConstant::LONG_RESULTS_NUMBER);

        return $this->searchable->search($query)->getAggregations();
    }

    /**
     * {@inheritdoc}
     */
    public function findKeyword(Event $event, string $filter, array $defaultFilters, string $locale): array
    {
        $filter = ForeignChar::transliterateString($filter);
        $builder = new SheetSearchQueryBuilder(
            $event,
            $defaultFilters,
            $locale
        );

        $matchKeyword = new Query\Term(['keywords.label_autocomplete' => $filter]);
        $matchLocale  = new Query\Match('keywords.locale', $locale);

        $filterKeywordQuery = new Query\BoolQuery();
        $filterKeywordQuery->addMust($matchKeyword);
        $filterKeywordQuery->addMust($matchLocale);

        $filterEventQuery = new Query\Match('event', $event->getId());

        $keywordAggregations = new Terms('keyword');
        $keywordAggregations->setField('keywords.label');
        $keywordAggregations->setSize(10);

        $filterKeywords = new Filter('keywords_filter');
        $filterKeywords->addAggregation($keywordAggregations);
        $filterKeywords->setFilter($filterKeywordQuery);

        $nestedKeywordsAggregations = new Nested('keywords_aggs', 'keywords');
        $nestedKeywordsAggregations->addAggregation($filterKeywords);

        $filterKeywordsEvent = new Filter('keywords', $filterEventQuery);
        $filterKeywordsEvent->addAggregation($nestedKeywordsAggregations);

        $query = new Query($builder->getQuery());
        $query->addAggregation($filterKeywordsEvent)
            ->addAggregation($this->findSheetNameQuery($event, $filter))
            ->setSize(ElasticSearchConstant::LONG_RESULTS_NUMBER);

        return $this->searchable->search($query)->getAggregations();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): array {
        return $this->searchAggregations(
            $event,
            $locale,
            $filters,
            $filterToRemove,
            self::ES_FIELD_TYPE,
            $nomenclatureItems,
            $availableSlotIds,
            $sheetsToExclude
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): array {
        return $this->searchAggregations(
            $event,
            $locale,
            $filters,
            $filterToRemove,
            self::ES_FIELD_CATEGORIES,
            $nomenclatureItems,
            $availableSlotIds,
            $sheetsToExclude
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationCategoryAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove
    ): array {
        return $this->searchAggregations(
            $event,
            $locale,
            $filters,
            $filterToRemove,
            self::ES_FIELD_ORGANIZATION_CATEGORY
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPositionAggregations(Event $event, string $locale, array $filters, string $filterToRemove): array
    {
        return $this->searchAggregations(
            $event,
            $locale,
            $filters,
            $filterToRemove,
            self::ES_FIELD_POSITION
        );
    }

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     * @param null|RuleInterface $condition
     *
     * @return Result[]
     */
    private function getSearchResults(Event $event, array $filters, string $locale, ?RuleInterface $condition = null): array
    {
        $builder = new SheetSearchQueryBuilder($event, $filters, $locale);
        $queryToArray = ['query' => $builder->getQuery()->toArray()];

        if ($condition instanceof Condition) {
            $rules = $this->conditionRulesTransformer->transform($condition);

            if ($rules) {
                $queryToArray['query']['bool']['must'][] = $rules;
            }
        }

        $query = new Query();
        $query->setRawQuery($queryToArray);
        $query->addSort(['sheetName' => 'asc']);

        $options = ['size' => ElasticSearchConstant::LONG_RESULTS_NUMBER];

        return $this->searchable->search($query, $options)->getResults();
    }

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     * @param string $elasticField
     * @param array  $nomenclatureItems
     * @param array  $availableSlotIds
     * @param array  $sheetsToExclude
     *
     * @return array
     */
    private function searchAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove,
        string $elasticField,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): array {
        // remove filter
        unset($filters[$filterToRemove]);

        $builder = new SheetSearchQueryBuilder(
            $event,
            $filters,
            $locale,
            1,
            $nomenclatureItems,
            $availableSlotIds,
            $sheetsToExclude
        );
        $query = new Query($builder->getQuery());

        if (self::ES_FIELD_POSITION === $elasticField) {
            $query->addAggregation($this->getNestedAggregation($elasticField, self::ES_PATH_POSITION));
        } elseif (self::ES_FIELD_CATEGORIES === $elasticField) {
            $query->addAggregation($this->getNestedAggregation($elasticField, self::ES_PATH_CATEGORIES));
        } else {
            $query->addAggregation($this->getAggregation($elasticField));
        }

        $query->setSize(ElasticSearchConstant::LONG_RESULTS_NUMBER);

        $result = $this->searchable->search($query);

        return $result->getAggregations();
    }

    /**
     * @param string $field
     *
     * @return Terms
     */
    private function getAggregation(string $field): Terms
    {
        $aggregation = new Terms($field);
        $aggregation->setField($field);
        $aggregation->setSize(ElasticSearchConstant::LONG_RESULTS_NUMBER);

        return $aggregation;
    }

    /**
     * @param string $field
     * @param string $path
     *
     * @return Nested
     */
    private function getNestedAggregation(string $field, string $path): Nested
    {
        $nested = new Nested($field, $path);
        $nested->addAggregation($this->getAggregation($field));

        return $nested;
    }

    /**
     * @param Event  $event
     * @param string $filter
     *
     * @return Filter
     */
    private function findCityQuery(Event $event, string $filter): Filter
    {
        $matchCity = new Query\BoolQuery();
        $matchCity->addMust(new Query\Match('event', $event->getId()));
        $matchCity->addMust(new Query\Match('city_autocomplete', $filter));

        $citiesAggregations = new Terms('cities');
        $citiesAggregations->setField('city');
        $citiesAggregations->setSize(10);

        $cities = new Filter('cities_aggs');
        $cities->addAggregation($citiesAggregations);
        $cities->setFilter($matchCity);

        return $cities;
    }

    /**
     * @param Event  $event
     * @param string $filter
     * @param string $locale
     *
     * @return Filter
     */
    private function findCountryQuery(Event $event, string $filter, string $locale): Filter
    {
        $filterEventQuery = new Query\Match('event', $event->getId());

        // country
        $matchCountry = new Query\Match('country.label_autocomplete', $filter);
        $matchLocale  = new Query\Match('country.locale', $locale);

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust($matchCountry);
        $boolQuery->addMust($matchLocale);

        $countryAggregations = new Terms('countries');
        $countryAggregations->setField('country.label');
        $countryAggregations->setSize(10);

        $filterCountries = new Filter('countries_filter');
        $filterCountries->addAggregation($countryAggregations);
        $filterCountries->setFilter($boolQuery);

        $nestedCountryAggregations = new Nested('countries', 'country');
        $nestedCountryAggregations->addAggregation($filterCountries);

        $filterCountryEvent = new Filter('countries_aggs', $filterEventQuery);
        $filterCountryEvent->addAggregation($nestedCountryAggregations);

        return $filterCountryEvent;
    }

    /**
     * @param Event  $event
     * @param string $filter
     *
     * @return Filter
     */
    private function findSheetNameQuery(Event $event, string $filter): Filter
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Term(['sheetName.autocomplete' => $filter]));
        $boolQuery->addMust(new Query\Match('event', $event->getId()));

        $sheetAggregation = new Terms('sheetname');
        $sheetAggregation->setField('sheetName.raw');
        $sheetAggregation->setSize(ElasticSearchConstant::LONG_RESULTS_NUMBER);

        $filterSheetName = new Filter('sheet', $boolQuery);
        $filterSheetName->addAggregation($sheetAggregation);

        return $filterSheetName;
    }
}
