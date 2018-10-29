<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use Elastica\Query;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\ElasticSearchConstant;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Application\Query\User\UserEventListViews\GetUserIdsByEventQuery;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\ConditionRulesTransformerInterface;
use Proximum\Vimeet\Domain\UserEventView\UserEventListView;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\SearchAdapter;

class GetUserIdsByEvent
{
    /** @var SearchAdapter */
    private $searchAdapter;

    /** @var ElasticDocumentsToUserEventListViewsTransformer */
    private $elasticDocumentsToUserEventListViewsTranformer;

    /** @var ConditionRulesTransformerInterface */
    private $conditionRulesTransformer;

    public function __construct(
        SearchAdapter $searchAdapter,
        ElasticDocumentsToUserEventListViewsTransformer $elasticDocumentsToUserEventListViewsTranformer,
        ConditionRulesTransformerInterface $conditionRulesTransformer
    ) {
        $this->searchAdapter = $searchAdapter;
        $this->elasticDocumentsToUserEventListViewsTranformer = $elasticDocumentsToUserEventListViewsTranformer;
        $this->conditionRulesTransformer = $conditionRulesTransformer;
    }

    public function handle(GetUserIdsByEventQuery $getUserIdsQuery): iterable
    {
        $ids = [];
        $conditions = [];

        $conditions[] = [
            'term' => [
                TypesMapping::USER_EVENT_VIEW_EVENT_ID => [
                    'value' => $getUserIdsQuery->event->getId(),
                ],
            ],
        ];

        if ($getUserIdsQuery->condition) {
            $conditions[] = $this->conditionRulesTransformer->transform($getUserIdsQuery->condition);
        }

        $query = new Query(
            [
                'query' => [
                    'bool' => [
                        'must' => $conditions,
                    ],
                ],
                'sort' => [
                    [TypesMapping::USER_EVENT_VIEW_LASTNAME => 'asc'],
                    [TypesMapping::USER_EVENT_VIEW_FIRSTNAME => 'asc'],
                ],
                'size' => ElasticSearchConstant::LONG_RESULTS_NUMBER,
            ]
        );

        $resultSet = $this->searchAdapter->handleQuery(TypesMapping::getTypeByClass(UserEventView::class), $query);
        $results = $this->elasticDocumentsToUserEventListViewsTranformer->handle($resultSet->getDocuments(), $getUserIdsQuery->locale);

        /** @var UserEventListView $result */
        foreach ($results as $result) {
            $ids[] = $result->userId;
        }

        return $ids;
    }
}
