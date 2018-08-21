<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView\GetUserEventListViewsByEventInterface;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\ConditionRulesTransformerInterface;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\SearchAdapter;
use Proximum\Vimeet\Infrastructure\Elastica\Persister\TypesMapping;

class GetUserEventListViewsByEvent implements GetUserEventListViewsByEventInterface
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

    public function handle(Event $event, int $page, string $locale, ?Condition $condition): PaginatedResult
    {
        $initialQuery[] = [
            'term' => [
                'eventId' => [
                    'value' => $event->getId(),
                ],
            ],
        ];

        if ($condition) {
            $initialQuery[] = $this->conditionRulesTransformer->transform($condition);
        }

        $query = new \Elastica\Query(
            [
                'query' => [
                    'bool' => [
                        'must' => $initialQuery,
                    ],
                ],
                'sort' => [
                    ['lastName' => 'asc'],
                    ['firstName' => 'asc'],
                ],
                'from' => ($page - 1) * self::RESULTS_NUMBER_BY_PAGE,
                'size' => self::RESULTS_NUMBER_BY_PAGE,
            ]
        );

        $resultSet = $this->searchAdapter->handleQuery(TypesMapping::getTypeByClass(UserEventView::class), $query);

        return new PaginatedResult(
            $this->elasticDocumentsToUserEventListViewsTranformer->handle($resultSet->getDocuments(), $locale),
            $page,
            self::RESULTS_NUMBER_BY_PAGE,
            $resultSet->getTotalHits()
        );
    }
}
