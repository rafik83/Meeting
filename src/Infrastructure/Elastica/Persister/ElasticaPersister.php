<?php

namespace Proximum\Vimeet\Infrastructure\Elastica\Persister;

use Elastica\Client;
use Elastica\Document;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\ElasticSearchPersisterInterface;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;

class ElasticaPersister implements ElasticSearchPersisterInterface
{
    /** @var Client */
    private $client;

    /** @var ElasticaMapping */
    private $mapping;

    /** @var string */
    private $index;

    /** @var SerializerAdapterInterface */
    private $serializer;

    public function __construct(
        Client $client,
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

        $response = null;
        foreach (array_chunk($objects, 100, false) as $chunkObjects) {
            $response = $elasticaType->addDocuments($this->getDocuments($identifierProperty, $chunkObjects));
        }

        $elasticaType->getIndex()->refresh();

        if (null === $response) {
            return [];
        }

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
        return $this->getIndex()->getType($typeName);
    }

    private function getIndex(): \Elastica\Index
    {
        return $this->client->getIndex($this->index);
    }

    /**
     * @param Document[] $objects
     */
    private function getDocuments(string $identifierProperty, array &$objects): array
    {
        $documents = [];

        foreach ($objects as $object) {
            if (!isset($object->$identifierProperty)) {
                throw new \InvalidArgumentException('Missing identifier column');
            }

            $documents[] = new Document($object->$identifierProperty, $this->serializer->normalize($object));
        }

        return $documents;
    }
}
