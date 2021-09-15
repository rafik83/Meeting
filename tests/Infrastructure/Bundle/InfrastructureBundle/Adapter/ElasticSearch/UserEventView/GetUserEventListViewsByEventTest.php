<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use Elastica\Document;
use Elastica\Query;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\ConditionRulesToElasticTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorAnd;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\UserEventView\UserEventListView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\SearchAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView\ElasticDocumentsToUserEventListViewsTransformer;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView\GetUserEventListViewsByEvent;

class GetUserEventListViewsByEventTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $condition = new Condition(
            $event->reveal(),
            'fr',
            new LogicalOperatorAnd,
            [
                new Field('field', new ComparisonOperatorEqual, 'text', 'A1', 'fr')
            ]
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
                'from' => 100,
                'size' => 100,
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

        $expectedUserEventListViews = [
            new UserEventListView(
                42,
                1,
                'Korben',
                'DALLAS',
                'korben.dallas@fifth.element',
                'en',
                false,
                false,
                [['id' => 1337]]
            ),
            new UserEventListView(
                42,
                2,
                'Leeloo',
                'Ekbat de Sebat',
                'leeloo@fifth.element',
                'fr',
                false,
                false,
                [
                    ['id' => 1337],
                    ['id' => 4556],
                ]
            ),
        ];

        $expectedResult = new PaginatedResult(
            $expectedUserEventListViews,
            2,
            100,
            77
        );

        $resultSet = $this->prophesize(\Elastica\ResultSet::class);
        $resultSet->getDocuments()->shouldBeCalled()->willReturn($documents);
        $resultSet->getTotalHits()->shouldBeCalled()->willReturn(77);

        $searchAdapter = $this->prophesize(SearchAdapter::class);
        $searchAdapter
            ->handleQuery('user_event', $expectedQuery)
            ->shouldBeCalled()
            ->willReturn($resultSet->reveal())
        ;

        $elasticDocumentsToUserEventListViewsTransformer = $this->prophesize(
            ElasticDocumentsToUserEventListViewsTransformer::class
        );
        $elasticDocumentsToUserEventListViewsTransformer
            ->handle($documents, 'fr')
            ->shouldBeCalled()
            ->willReturn($expectedUserEventListViews)
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

        $getUserEventViewsByEvent = new GetUserEventListViewsByEvent(
            $searchAdapter->reveal(),
            $elasticDocumentsToUserEventListViewsTransformer->reveal(),
            $conditionRulesTransformer->reveal()
        );

        $this->assertEquals(
            $expectedResult,
            $getUserEventViewsByEvent->handle($event->reveal(), 2, 'fr', $condition)
        );
    }
}
