<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter\ElasticSearch;

interface ElasticSearchPersisterInterface
{
    public function persist(string $identifierProperty = 'id', array $objects = []): array;

    public function deleteIds(string $typeName, array $identifiers): array;
}
