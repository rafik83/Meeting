<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Elastica\Persister;

class ElasticaMapping
{
    public function setMapping(\Elastica\Type $elasticaType, array $properties): \Elastica\Response
    {
        $mapping = new \Elastica\Type\Mapping($elasticaType, $properties);

        return $mapping->send();
    }
}
