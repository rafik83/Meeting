<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\ConditionRules;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\ConditionRules\ConditionRulesParser;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotNull;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNull;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorAnd;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorOr;

class ConditionParserTest extends TestCase
{
    public function testHandle(): void
    {
        $conditions = [
            'condition' => 'AND',
            'rules' => [
                [
                    'field' => 'Activity',
                    'operator' => 'equal',
                    'value' => 'A1',
                ],
                [
                    'condition' => 'OR',
                    'rules' => [
                        [
                            'field' => 'Sector',
                            'operator' => 'in',
                            'value' => ['S1', 'S3'],
                        ],
                        [
                            'field' => 'Universe',
                            'operator' => 'equal',
                            'value' => 'U4',
                        ],
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'field' => 'LastName',
                                    'operator' => 'is_null',
                                    'value' => null,
                                ],
                                [
                                    'field' => 'FirstName',
                                    'operator' => 'is_not_null',
                                    'value' => 'mathieu',
                                ],
                            ]
                        ]
                    ],
                ],
            ],
        ];

        $expectedResult = new Condition(
            new LogicalOperatorAnd,
            [
                new Field('Activity', new ComparisonOperatorEqual, 'A1'),
                new Condition(
                    new LogicalOperatorOr,
                    [
                        new Field('Sector', new ComparisonOperatorIn, ['S1', 'S3']),
                        new Field('Universe', new ComparisonOperatorEqual, 'U4'),
                        new Condition(
                            new LogicalOperatorAnd,
                            [
                                new Field('LastName', new ComparisonOperatorNull, null),
                                new Field('FirstName', new ComparisonOperatorNotNull, 'mathieu'),
                            ]
                        ),
                    ]
                ),
            ]
        );

        $result = ConditionRulesParser::parse($conditions);

        $this->assertEquals($expectedResult, $result);
    }
}
