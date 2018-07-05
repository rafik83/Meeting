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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\SearchAdapter;
use Proximum\Vimeet\Infrastructure\Elastica\Persister\TypesMapping;

class GetUserEventListViewsByEvent implements GetUserEventListViewsByEventInterface
{
    /** @var SearchAdapter */
    private $searchAdapter;

    /** @var ElasticDocumentsToUserEventListViewsTransformer */
    private $elasticDocumentsToUserEventListViewsTranformer;

    public function __construct(
        SearchAdapter $searchAdapter,
        ElasticDocumentsToUserEventListViewsTransformer $elasticDocumentsToUserEventListViewsTranformer
    ) {
        $this->searchAdapter = $searchAdapter;
        $this->elasticDocumentsToUserEventListViewsTranformer = $elasticDocumentsToUserEventListViewsTranformer;
    }

    public function handle(Event $event, int $page, string $locale): array
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
                'sort' => [
                    ['lastName' => 'asc'],
                    ['firstName' => 'asc'],
                ],
                'from' => ($page - 1) * self::RESULTS_NUMBER_BY_PAGE,
                'size' => self::RESULTS_NUMBER_BY_PAGE,
            ]
        );

        $documents = $this->searchAdapter->handleQuery(TypesMapping::getTypeByClass(UserEventView::class), $query);

        return $this->elasticDocumentsToUserEventListViewsTranformer->handle($documents, $locale);
    }
}
