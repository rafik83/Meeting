<?php

namespace Proximum\Vimeet\Infrastructure\Elastica\Persister;

class ElasticaMapping
{
    public function setMapping(\Elastica\Type $elasticaType, array $properties): \Elastica\Response
    {
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($elasticaType);
        $mapping->setProperties($properties);

        return $mapping->send();
    }
}
