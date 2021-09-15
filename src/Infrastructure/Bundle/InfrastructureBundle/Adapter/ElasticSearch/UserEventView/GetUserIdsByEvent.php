<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use Elastica\Query;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\ElasticSearchConstant;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Application\Query\User\UserEventListViews\GetUserIdsByEventQuery;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\ConditionRulesTransformerInterface;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\SearchAdapter;

class GetUserIdsByEvent
{
    /** @var SearchAdapter */
    private $searchAdapter;

    /** @var ConditionRulesTransformerInterface */
    private $conditionRulesTransformer;

    public function __construct(
        SearchAdapter $searchAdapter,
        ConditionRulesTransformerInterface $conditionRulesTransformer
    ) {
        $this->searchAdapter = $searchAdapter;
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
            $rules = $this->conditionRulesTransformer->transform($getUserIdsQuery->condition);

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
                'size' => ElasticSearchConstant::LONG_RESULTS_NUMBER,
            ]
        );

        $documents = $this->searchAdapter->handleQuery(TypesMapping::getTypeByClass(UserEventView::class), $query);

        foreach ($documents->getDocuments() as $document) {
            $ids[] = $document->getData()[TypesMapping::USER_EVENT_VIEW_USER_ID];
        }

        return $ids;
    }
}
