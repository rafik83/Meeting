<?php

namespace Proximum\Vimeet\Application\Adapter\ElasticSearch;

interface ElasticSearchPersisterInterface
{
    public function persist(string $identifierProperty = 'id', array $objects = []): array;

    public function deleteIds(string $typeName, array $identifiers): array;
}
