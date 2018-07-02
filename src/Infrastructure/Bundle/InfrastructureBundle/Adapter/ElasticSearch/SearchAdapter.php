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
use Proximum\Vimeet\Infrastructure\Elastica\Transformer\UserEventView\UserEventViewConstant;

class SearchAdapter
{
    /** @var Client */
    private $client;

    /** @var string */
    private $index;

    public function __construct(Client $client, string $index)
    {
        $this->client = $client;
        $this->index = $index;
    }

    public function getSheets(Event $event)
    {
        $search = new \Elastica\Search($this->client);
        $search->addIndex($this->index);
        $search->addType(UserEventViewConstant::TYPE);

        $query = new \Elastica\Query(
            [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'term' => [
                                    'event' => [
                                        'value' => $event->getId(),
                                    ],
                                ],
                            ],
                            [
                                'term' => [
                                    'enabled' => [
                                        'value' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'from' => 0,
                'size' => 100,
            ]
        );

        $search->setQuery($query);
        $resultSet = $search->search();

        dump($resultSet->getDocuments());
    }
}
