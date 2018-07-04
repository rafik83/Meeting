<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Elastica\Persister;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\ElasticSearchPersisterInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;

class ElasticaPersister implements ElasticSearchPersisterInterface
{
    /** @var \Elastica\Client */
    private $client;

    /** @var string */
    private $index;

    /** @var SerializerAdapterInterface */
    private $serializer;

    public function __construct(\Elastica\Client $client, string $index, SerializerAdapterInterface $serializer)
    {
        $this->client = $client;
        $this->index = $index;
        $this->serializer = $serializer;
    }

    public function persist($identifierProperty = 'id', array $objects = []): array
    {
        if (empty($objects)) {
            return [];
        }

        $class = \get_class(reset($objects));

        if (!isset(TypesMapping::AVAILABLE_TYPES[$class])) {
            throw new \InvalidArgumentException(sprintf('ElasticSearch type for %s is not available', $class));
        }

        $objectType = TypesMapping::AVAILABLE_TYPES[$class];

        $elasticaIndex = $this->client->getIndex($this->index);
        $elasticaType = $elasticaIndex->getType($objectType['type']);
        $this->setMapping($elasticaType, $objectType['properties']);

        $response = $elasticaType->addDocuments($this->getDocuments($identifierProperty, $objects));
        $elasticaType->getIndex()->refresh();

        return $response->getData();
    }

    private function getDocuments(string $identifierProperty, array &$objects)
    {
        $documents = [];

        foreach ($objects as $object) {
            if (!isset($object->$identifierProperty)) {
                throw new \InvalidArgumentException('Missing identifier column');
            }

            $documents[] = new \Elastica\Document($object->$identifierProperty, $this->serializer->normalize($object));
        }

        return $documents;
    }

    private function setMapping(\Elastica\Type $elasticaType, array $properties)
    {
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($elasticaType);
        $mapping->setProperties($properties);
        $mapping->send();
    }
}
