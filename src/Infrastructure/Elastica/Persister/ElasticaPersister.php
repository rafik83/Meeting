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

    /** @var ElasticaMapping */
    private $mapping;

    /** @var string */
    private $index;

    /** @var SerializerAdapterInterface */
    private $serializer;

    public function __construct(
        \Elastica\Client $client,
        ElasticaMapping $mapping,
        string $index,
        SerializerAdapterInterface $serializer
    ) {
        $this->client = $client;
        $this->mapping = $mapping;
        $this->index = $index;
        $this->serializer = $serializer;
    }

    public function persist(string $identifierProperty = 'id', array $objects = []): array
    {
        if (empty($objects)) {
            return [];
        }

        $object = reset($objects);

        if (false === $object) {
            return [];
        }

        $typeMapping = $this->getObjectTypeMapping($object);
        $elasticaType = $this->getType($typeMapping['type']);
        $this->mapping->setMapping($elasticaType, $typeMapping['properties']);
        $response = $elasticaType->addDocuments($this->getDocuments($identifierProperty, $objects));
        $elasticaType->getIndex()->refresh();

        return $response->getData();
    }

    public function deleteIds(string $typeName, array $identifiers): array
    {
        if (empty($identifiers)) {
            return [];
        }

        $elasticaType = $this->getType($typeName);
        $response = $this->client->deleteIds($identifiers, $this->index, $elasticaType);

        return $response->getData();
    }

    private function getObjectTypeMapping($object): array
    {
        $class = \get_class($object);

        if (!isset(TypesMapping::AVAILABLE_TYPES[$class])) {
            throw new \InvalidArgumentException(sprintf('ElasticSearch type for %s is not available', $class));
        }

        return TypesMapping::AVAILABLE_TYPES[$class];
    }

    private function getType(string $typeName): \Elastica\Type
    {
        $elasticaIndex = $this->client->getIndex($this->index);

        return $elasticaIndex->getType($typeName);
    }

    /**
     * @param \Elastica\Document[] $objects
     */
    private function getDocuments(string $identifierProperty, array &$objects): array
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
}
