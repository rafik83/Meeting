<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Elastica\Persister;

use Elastica\Client;
use Elastica\Document;
use Elastica\Type\Mapping;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\ElasticSearchPersisterInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;

class ElasticaPersister implements ElasticSearchPersisterInterface
{
    /** @var Client */
    private $client;

    /** @var string */
    private $index;

    /** @var SerializerAdapterInterface */
    private $serializer;

    public function __construct(Client $client, string $index, SerializerAdapterInterface $serializer)
    {
        $this->client = $client;
        $this->index = $index;
        $this->serializer = $serializer;
    }

    public function persist($id, $object): array
    {
        $class = \get_class($object);

        if (!isset(TypesMapping::AVAILABLE_TYPES[$class])) {
            throw new \InvalidArgumentException(sprintf('ElasticSearch type for %s is not available', $class));
        }

        $type = TypesMapping::AVAILABLE_TYPES[$class];

        $elasticaIndex = $this->client->getIndex($this->index);
        $elasticaType = $elasticaIndex->getType($type['type']);

        $mapping = new Mapping();
        $mapping->setType($elasticaType);
        $mapping->setProperties($type['properties']);
        $mapping->send();

        $response = $elasticaType->addDocument(new Document($id, $this->serializer->normalize($object)));

        return $response->getData();
    }
}
