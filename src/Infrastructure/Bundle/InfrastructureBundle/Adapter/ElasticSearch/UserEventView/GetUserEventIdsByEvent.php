<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\ElasticSearchConstant;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView\GetUserEventIdsByEventInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\SearchAdapter;
use Proximum\Vimeet\Infrastructure\Elastica\Persister\TypesMapping;

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
        $query = new \Elastica\Query(
            [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'term' => [
                                    'eventId' => [
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
