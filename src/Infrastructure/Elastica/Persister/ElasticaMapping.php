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
    public function setMapping(\Elastica\Type $elasticaType, array $properties, array $params): \Elastica\Response
    {
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($elasticaType);
        $mapping->setProperties($properties);

        foreach ($params as $param => $value) {
            $mapping->setParam($param, $value);
        }

        return $mapping->send();
    }
}
