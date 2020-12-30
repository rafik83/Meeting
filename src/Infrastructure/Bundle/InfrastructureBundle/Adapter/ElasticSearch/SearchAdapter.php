<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch;

use Elastica\Client;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Search;

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

    public function handleQuery(string $type, Query $query): ResultSet
    {
        $search = new Search($this->client);
        $search->addIndex($this->index);
        $search->addType($type);
        $search->setQuery($query);

        return $search->search();
    }
}
