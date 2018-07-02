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
    /**
     * @param string|int $id
     * @param object     $object
     *
     * @return array
     */
    public function persist($id, $object): array;
}
