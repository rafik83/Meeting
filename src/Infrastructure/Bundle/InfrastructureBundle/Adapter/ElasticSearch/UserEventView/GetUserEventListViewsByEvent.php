<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use Elastica\Query;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView\GetUserEventListViewsByEventInterface;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\ConditionRulesTransformerInterface;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\SearchAdapter;

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
        $conditions = [];
        $conditions[] = [
            'term' => [
                TypesMapping::USER_EVENT_VIEW_EVENT_ID => [
                    'value' => $event->getId(),
                ],
            ],
        ];

        if ($condition) {
            $rules = $this->conditionRulesTransformer->transform($condition);

            if ($rules) {
                $conditions[] = $rules;
            }
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
