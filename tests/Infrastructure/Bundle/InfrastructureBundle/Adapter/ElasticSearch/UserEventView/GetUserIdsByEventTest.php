<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use Elastica\Document;
use Elastica\Query;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\User\UserEventListViews\GetUserIdsByEventQuery;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\ConditionRulesToElasticTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorAnd;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\SearchAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView\GetUserIdsByEvent;

class GetUserIdsByEventTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);

        $condition = new Condition(
            $event->reveal(),
            'fr',
            new LogicalOperatorAnd,
            [ new Field('field', new ComparisonOperatorEqual, 'text', 'A1', 'FR') ]
        );

        $event->getId()->willReturn(42);

        $expectedQuery = new Query(
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
                            [
                                'bool' => [
                                    'must' => [
                                        [
                                            'query_string' => [
                                                'default_field' => 'field',
                                                'query' => 'A1',
                                            ],
                                        ],
                                    ],
                                ],
                            ]
                        ],
                    ],
                ],
                'sort' => [
                    ['lastName' => 'asc'],
                    ['firstName' => 'asc'],
                ],
                'size' => 100000000,
            ]
        );

        $documents = [
            new Document(
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
            new Document(
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


        $resultSet = $this->prophesize(\Elastica\ResultSet::class);
        $resultSet->getDocuments()->shouldBeCalled()->willReturn($documents);

        $searchAdapter = $this->prophesize(SearchAdapter::class);
        $searchAdapter
            ->handleQuery('user_event', $expectedQuery)
            ->shouldBeCalled()
            ->willReturn($resultSet->reveal())
        ;


        $conditionRulesTransformer = $this->prophesize(ConditionRulesToElasticTransformer::class);
        $conditionRulesTransformer->transform($condition)->willReturn(
            [
                'bool' => [
                    'must' => [
                        [
                            'query_string' => [
                                'default_field' => 'field',
                                'query' => 'A1',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $getUserIdsByEvent = new GetUserIdsByEvent(
            $searchAdapter->reveal(),
            $conditionRulesTransformer->reveal()
        );

        $this->assertEquals(
            [1, 2],
            $getUserIdsByEvent->handle(new GetUserIdsByEventQuery($event->reveal(), 'fr', $condition))
        );
    }
}
