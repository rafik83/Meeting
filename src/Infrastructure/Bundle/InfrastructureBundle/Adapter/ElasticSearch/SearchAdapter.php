<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch;

use Elastica\Client;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Search;

class SearchAdapter
{
    private Client $client;

    private string $indexPrefix;

    public function __construct(Client $client, string $indexPrefix)
    {
        $this->client = $client;
        $this->indexPrefix = $indexPrefix;
    }

    public function handleQuery(string $type, Query $query): ResultSet
    {
        $search = new Search($this->client);
        $search->addIndex($this->indexPrefix.'_'.$type);
        $search->addType($type);
        $search->setQuery($query);

        return $search->search();
    }
}
