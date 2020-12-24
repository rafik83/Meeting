<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Elastica\Persister;

use Elastica\Bulk\ResponseSet;
use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
use Elastica\Type;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Infrastructure\Elastica\Persister\ElasticaMapping;
use Proximum\Vimeet\Infrastructure\Elastica\Persister\ElasticaPersister;

class ElasticaPersisterTest extends TestCase
{
    public function testPersist()
    {
        $index = 'app_prod';

        $userEventView = new UserEventView(
            42,
            3,
            'Korben',
            'DALLAS',
            'korben.dallas@example.net',
            'fr',
            false,
            false,
            [['id' => 1337]],
            []
        );
        $normalizedUserEventView = [
            'firstName' => 'Korben',
            'lastName' => 'DALLAS',
            'email' => 'korben.dallas@example.net',
            'locale' => 'fr',
        ];

        $response = $this->prophesize(ResponseSet::class);
        $response->getData()->shouldBeCalled()->willReturn(['response' => 'ok']);

        $elasticaType = $this->prophesize(Type::class);

        $elasticaType
            ->addDocuments([new Document('42_3', $normalizedUserEventView)])
            ->shouldBeCalled()
            ->willReturn($response->reveal())
        ;

        $elasticaIndex = $this->prophesize(Index::class);
        $elasticaIndex->getType('user_event')->shouldBeCalled()->willReturn($elasticaType->reveal());

        $elasticaIndex->refresh()->shouldBeCalled();

        $elasticaType->getIndex()->willReturn($elasticaIndex->reveal());

        $client = $this->prophesize(Client::class);
        $client->getIndex($index)->shouldBeCalled()->willReturn($elasticaIndex->reveal());

        $elasticaMapping = $this->prophesize(ElasticaMapping::class);
        $elasticaMapping
            ->setMapping(
                $elasticaType,
                TypesMapping::AVAILABLE_TYPES[UserEventView::class]['properties']
            )
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
        $elasticaPersister->persist('uid', [$userEventView]);
    }

    public function testDeleteIds()
    {
        $index = 'app_prod';
        $identifiers = ['1_2', '42_1337'];

        $response = $this->prophesize(ResponseSet::class);
        $response->getData()->shouldBeCalled()->willReturn(['response' => 'ok']);

        $elasticaType = $this->prophesize(Type::class);
        $elasticaIndex = $this->prophesize(Index::class);

        $elasticaIndex->getType('user_event')->shouldBeCalled()->willReturn($elasticaType->reveal());

        $client = $this->prophesize(Client::class);
        $client->getIndex($index)->shouldBeCalled()->willReturn($elasticaIndex->reveal());
        $client
            ->deleteIds($identifiers, $index, $elasticaType->reveal())
            ->shouldBeCalled()
            ->willReturn($response->reveal())
        ;

        $elasticaPersister = new ElasticaPersister(
            $client->reveal(),
            $this->prophesize(ElasticaMapping::class)->reveal(),
            $index,
            $this->prophesize(SerializerAdapterInterface::class)->reveal()
        );
        $elasticaPersister->deleteIds('user_event', $identifiers);
    }
}
