<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\ConditionRules\Transformer\Elastic;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\ConditionRulesToElasticTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorBeginsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotNull;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorAnd;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorOr;

class ConditionRulesToElasticTransformerTest extends TestCase
{

    public function testTransform(): void
    {
        $condition = new Condition(
            new LogicalOperatorAnd,
            [
                new Field('Activity', new ComparisonOperatorEqual, 'text', 'A1'),
                new Condition(
                    new LogicalOperatorOr,
                    [
                        new Field('Sector', new ComparisonOperatorContains, 'text', 'S1'),
                        new Field('Universe', new ComparisonOperatorBeginsWith(), 'text', 'U'),
                        new Condition(
                            new LogicalOperatorAnd,
                            [
                                new Field('LastName', new ComparisonOperatorNotContains(), 'text', 'test'),
                                new Field('FirstName', new ComparisonOperatorNotNull, 'text', 'mathieu'),
                            ]
                        ),
                    ]
                ),
            ]
        );

        $expectedResult = [
            'bool' => [
                'must' => [
                    [
                        'query_string' => [
                            'default_field' => 'Activity',
                            'query' => 'A1',
                        ],
                    ],
                    [
                        'bool' => [
                            'should' => [
                                [
                                    'query_string' => [
                                        'default_field' => 'Sector',
                                        'query' => '*S1*',
                                    ],
                                ],
                                [
                                    'query_string' => [
                                        'default_field' => 'Universe',
                                        'query' => 'U*',
                                    ],
                                ],
                                [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'bool' => [
                                                    'must_not' => [
                                                        'query_string' => [
                                                            'default_field' => 'LastName',
                                                            'query' => '*test*',
                                                        ]
                                                    ]
                                                ]
                                            ],
                                            [
                                                'constant_score' => [
                                                    'filter' => [
                                                        'bool' => [
                                                            'must' => [
                                                                'exists' => [
                                                                    'field' => 'FirstName',
                                                                ]
                                                            ]
                                                        ]
                                                    ],
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $transformer = new ConditionRulesToElasticTransformer();
        $result = $transformer->transform($condition);

        $this->assertSame($expectedResult, $result);
    }
}
