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

    public function handleQuery(string $type, \Elastica\Query $query): \Elastica\ResultSet
    {
        $search = new \Elastica\Search($this->client);
        $search->addIndex($this->index);
        $search->addType($type);
        $search->setQuery($query);

        return $search->search();
    }
}
