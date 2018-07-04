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
        $this->mapping->setMapping($elasticaType, $objectType['properties']);

        $response = $elasticaType->addDocuments($this->getDocuments($identifierProperty, $objects));
        $elasticaIndex->refresh();

        return $response->getData();
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
