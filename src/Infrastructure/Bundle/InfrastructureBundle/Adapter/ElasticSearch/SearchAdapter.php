<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch;

use Elastica\Client;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Infrastructure\Elastica\Persister\TypesMapping;

class SearchAdapter
{
    const RESULTS_NUMBER_BY_PAGE = 100;

    /** @var Client */
    private $client;

    /** @var string */
    private $index;

    public function __construct(Client $client, string $index)
    {
        $this->client = $client;
        $this->index = $index;
    }

    /**
     * @return UserEventView[]
     */
    public function getUsersByEvent(Event $event, int $page): array
    {
        $search = new \Elastica\Search($this->client);
        $search->addIndex($this->index);
        $search->addType(TypesMapping::getTypeByClass(UserEventView::class));

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

        $search->setQuery($query);
        $resultSet = $search->search();

        $userEventViews = [];

        /** @var \Elastica\Document $document */
        foreach ($resultSet->getDocuments() as $document) {
            $data = $document->getData();

            $userEventViews[] = new UserEventView(
                $data['eventId'],
                $data['userId'],
                $data['firstName'],
                $data['lastName'],
                $data['email'],
                $data['sheets']
            );
        }

        return $userEventViews;
    }
}
