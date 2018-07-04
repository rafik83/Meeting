<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Elastica\Persister;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Infrastructure\Elastica\Persister\ElasticaMapping;
use Proximum\Vimeet\Infrastructure\Elastica\Persister\ElasticaPersister;
use Proximum\Vimeet\Infrastructure\Elastica\Persister\TypesMapping;

class ElasticaPersisterTest extends TestCase
{
    public function testPersist()
    {
        $index = 'app_prod';

        $userEventView = new UserEventView(42, 3, 'Korben', 'DALLAS', 'korben.dallas@example.net', 1337);
        $normalizedUserEventView = [
            'firstName' => 'Korben',
            'lastName' => 'DALLAS',
            'email' => 'korben.dallas@example.net',
        ];

        $response = $this->prophesize(\Elastica\Bulk\ResponseSet::class);
        $response->getData()->shouldBeCalled()->willReturn(['response' => 'ok']);

        $elasticaType = $this->prophesize(\Elastica\Type::class);
        $elasticaType
            ->addDocuments([new \Elastica\Document('42_3', $normalizedUserEventView)])
            ->shouldBeCalled()
            ->willReturn($response->reveal())
        ;

        $elasticaIndex = $this->prophesize(\Elastica\Index::class);
        $elasticaIndex->getType('user_event')->shouldBeCalled()->willReturn($elasticaType->reveal());
        $elasticaIndex->refresh()->shouldBeCalled();

        $client = $this->prophesize(\Elastica\Client::class);
        $client->getIndex($index)->shouldBeCalled()->willReturn($elasticaIndex->reveal());

        $elasticaMapping = $this->prophesize(ElasticaMapping::class);
        $elasticaMapping
            ->setMapping($elasticaType, TypesMapping::AVAILABLE_TYPES[UserEventView::class]['properties'])
            ->shouldBeCalled()
        ;

        $serializer = $this->prophesize(SerializerAdapterInterface::class);
        $serializer->normalize($userEventView)->shouldBeCalled()->willReturn($normalizedUserEventView);

        $elasticaPersister = new ElasticaPersister(
            $client->reveal(),
            $elasticaMapping->reveal(),
            $index,
            $serializer->reveal()
        );
        $elasticaPersister->persist('id', [$userEventView]);
    }
}
