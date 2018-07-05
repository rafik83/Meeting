<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView\GetUserEventViewsByEventInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\UserEventView\UserEventListView;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\SearchAdapter;
use Proximum\Vimeet\Infrastructure\Elastica\Persister\TypesMapping;

class GetUserEventViewsByEvent implements GetUserEventViewsByEventInterface
{
    /** @var SearchAdapter */
    private $searchAdapter;

    public function __construct(SearchAdapter $searchAdapter)
    {
        $this->searchAdapter = $searchAdapter;
    }

    public function handle(Event $event, int $page): array
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

        $userEventViews = [];
        $documents = $this->searchAdapter->handleQuery(TypesMapping::getTypeByClass(UserEventView::class), $query);

        foreach ($documents as $document) {
            $data = $document->getData();

            $userEventViews[] = new UserEventListView(
                $data['eventId'],
                $data['userId'],
                $data['firstName'],
                $data['lastName'],
                $data['email'],
                $data['locale'],
                $data['sheets']
            );
        }

        return $userEventViews;
    }
}
