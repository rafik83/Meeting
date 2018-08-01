<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter\Elasticsearch\Sheet;

use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Terms;
use Elastica\Query;
use Elastica\SearchableInterface;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\Sheet\TagFilterAggregator as TagFilterAggregatorInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Adapter\SheetSearchQueryBuilder;

class TagFilterAggregator implements TagFilterAggregatorInterface
{
    /** @var SearchableInterface */
    private $searchable;

    public function __construct(SearchableInterface $searchable)
    {
        $this->searchable = $searchable;
    }

    public function getAggregationsForTag(
        Event $event,
        string $tag,
        string $locale,
        array $filters,
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): array {
        // @todo remove filter on tag
        //unset($filters[$filterToRemove]);

        $builder = new SheetSearchQueryBuilder(
            $event,
            $filters,
            $locale,
            1,
            [],
            $availableSlotIds,
            $sheetsToExclude
        );
        $boolQuery = $builder->getQuery();
        $queryNestedTaggedData = new Query\Nested();
        $queryNestedTaggedData->setPath('nestedTaggedData');
        $queryNestedTaggedData->setQuery(new Query\Term(
            [
            'nestedTaggedData.tag' => [
                'value' => $tag,
            ]
        ]));

        $boolQuery->addMust($queryNestedTaggedData);
        $query = new Query($boolQuery);

        $aggregation = new Nested('nestedTaggedData', 'nestedTaggedData.values');

        $filterQuery = new Query\BoolQuery();
        $filterQuery->addMust(new Query\Term(['nestedTaggedData.values.tag' => $tag]));
        $subAggregation = new Filter('nestedTaggedData_nested_values_filter', $filterQuery);

        $terms = new Terms('nestedTaggedData_nested_values_terms');
        $terms->setField('nestedTaggedData.values.value');
        $terms->setSize(0);
        $subAggregation->addAggregation($terms);

        $aggregation->addAggregation($subAggregation);
        $query->addAggregation($aggregation);

        $query->setSize(0);
        $result = $this->searchable->search($query);

        $resultAggregations = $result->getAggregations();

        return $resultAggregations['nestedTaggedData']
            ['nestedTaggedData_nested_values_filter']
            ['nestedTaggedData_nested_values_terms']
            ['buckets']
            ?? []
        ;
    }
}
