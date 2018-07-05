<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\UserEventView\UserEventListView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\SearchAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView\ElasticDocumentsToUserEventListViewsTransformer;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView\GetUserEventListViewsByEvent;

class GetUserEventListViewsByEventTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(42);

        $expectedQuery = new \Elastica\Query(
            [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'term' => [
                                    'eventId' => [
                                        'value' => 42,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'sort' => [
                    ['lastName' => 'asc'],
                    ['firstName' => 'asc'],
                ],
                'from' => 100,
                'size' => 100,
            ]
        );

        $documents = [
            new \Elastica\Document(
                '42_1',
                [
                    'eventId' => 42,
                    'userId' => 1,
                    'firstName' => 'Korben',
                    'lastName' => 'DALLAS',
                    'email' => 'korben.dallas@fifth.element',
                    'locale' => 'en',
                    'sheets' => [
                        ['id' => 1337]
                    ],
                ]
            ),
            new \Elastica\Document(
                '42_2',
                [
                    'eventId' => 42,
                    'userId' => 2,
                    'firstName' => 'Leeloo',
                    'lastName' => 'Ekbat de Sebat',
                    'email' => 'leeloo@fifth.element',
                    'locale' => 'fr',
                    'sheets' => [
                        ['id' => 1337],
                        ['id' => 4556],
                    ],
                ]
            ),
        ];

        $expectedResult = [
            new UserEventListView(
                42,
                1,
                'Korben',
                'DALLAS',
                'korben.dallas@fifth.element',
                'en',
                [['id' => 1337]]
            ),
            new UserEventListView(
                42,
                2,
                'Leeloo',
                'Ekbat de Sebat',
                'leeloo@fifth.element',
                'fr',
                [
                    ['id' => 1337],
                    ['id' => 4556],
                ]
            ),
        ];

        $searchAdapter = $this->prophesize(SearchAdapter::class);
        $searchAdapter
            ->handleQuery('user_event', $expectedQuery)
            ->shouldBeCalled()
            ->willReturn($documents)
        ;

        $elasticDocumentsToUserEventListViewsTranformer = $this->prophesize(
            ElasticDocumentsToUserEventListViewsTransformer::class
        );
        $elasticDocumentsToUserEventListViewsTranformer
            ->handle($documents, 'fr')
            ->shouldBeCalled()
            ->willReturn($expectedResult)
        ;

        $getUserEventViewsByEvent = new GetUserEventListViewsByEvent(
            $searchAdapter->reveal(),
            $elasticDocumentsToUserEventListViewsTranformer->reveal()
        );

        $this->assertEquals(
            $expectedResult,
            $getUserEventViewsByEvent->handle($event->reveal(), 2, 'fr')
        );
    }
}
