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
            'logicalOperator' => 'AND',
            'rules' => [
                [
                    'field' => 'Activity',
                    'comparisonOperator' => 'equal',
                    'value' => 'A1',
                ],
                [
                    'logicalOperator' => 'OR',
                    'rules' => [
                        [
                            'field' => 'Sector',
                            'comparisonOperator' => 'in',
                            'value' => ['S1', 'S3'],
                        ],
                        [
                            'field' => 'Universe',
                            'comparisonOperator' => 'equal',
                            'value' => 'U4',
                        ],
                        [
                            'logicalOperator' => 'AND',
                            'rules' => [
                                [
                                    'field' => 'LastName',
                                    'comparisonOperator' => 'is_null',
                                    'value' => null,
                                ],
                                [
                                    'field' => 'FirstName',
                                    'comparisonOperator' => 'is_not_null',
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
