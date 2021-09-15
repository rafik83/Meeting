<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use Elastica\Query;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\ElasticSearchConstant;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView\GetUserEventIdsByEventInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\SearchAdapter;

class GetUserEventIdsByEvent implements GetUserEventIdsByEventInterface
{
    /** @var SearchAdapter */
    private $searchAdapter;

    public function __construct(SearchAdapter $searchAdapter)
    {
        $this->searchAdapter = $searchAdapter;
    }

    public function handle(Event $event): array
    {
        $query = new Query(
            [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'term' => [
                                    TypesMapping::USER_EVENT_VIEW_EVENT_ID => [
                                        'value' => $event->getId(),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'from' => 0,
                'size' => ElasticSearchConstant::LONG_RESULTS_NUMBER,
            ]
        );

        $resultSet = $this->searchAdapter->handleQuery(TypesMapping::getTypeByClass(UserEventView::class), $query);

        $ids = [];

        foreach ($resultSet as $result) {
            $ids[] = $result->getId();
        }

        return $ids;
    }
}
