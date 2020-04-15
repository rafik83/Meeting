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
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotNull;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNull;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorAnd;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorOr;
use Proximum\Vimeet\Domain\Model\Event;

class ConditionRulesParserTest extends TestCase
{
    public function testHandle(): void
    {
        $conditions = [
            'condition' => 'AND',
            'rules' => [
                [
                    'field' => 'Activity',
                    'operator' => 'equal',
                    'input' => 'text',
                    'value' => 'A1',
                ],
                [
                    'condition' => 'OR',
                    'rules' => [
                        [
                            'field' => 'Sector',
                            'operator' => 'contains',
                            'input' => 'checkbox',
                            'value' => ['S1', 'S3'],
                        ],
                        [
                            'field' => 'Universe',
                            'operator' => 'equal',
                            'input' => 'text',
                            'value' => 'U4',
                        ],
                        [
                            'condition' => 'AND',
                            'rules' => [
                                [
                                    'field' => 'LastName',
                                    'operator' => 'is_null',
                                    'input' => 'text',
                                    'value' => '',
                                ],
                                [
                                    'field' => 'FirstName',
                                    'operator' => 'is_not_null',
                                    'input' => 'text',
                                    'value' => 'mathieu',
                                ],
                            ]
                        ]
                    ],
                ],
            ],
        ];

        $event = $this->prophesize(Event::class);

        $expectedResult = new Condition(
            $event->reveal(),
            'FR',
            new LogicalOperatorAnd,
            [
                new Field('Activity', new ComparisonOperatorEqual, 'text', 'A1', 'FR'),
                new Condition(
                    $event->reveal(),
                    'FR',
                    new LogicalOperatorOr,
                    [
                        new Field('Sector', new ComparisonOperatorContains, 'checkbox', ['S1', 'S3'], 'FR'),
                        new Field('Universe', new ComparisonOperatorEqual, 'text', 'U4', 'FR'),
                        new Condition(
                            $event->reveal(),
                            'FR',
                            new LogicalOperatorAnd,
                            [
                                new Field('LastName', new ComparisonOperatorNull, 'text', '', 'FR'),
                                new Field('FirstName', new ComparisonOperatorNotNull, 'text', 'mathieu', 'FR'),
                            ]
                        ),
                    ]
                ),
            ]
        );

        $result = ConditionRulesParser::parse($event->reveal(), 'FR', $conditions);

        $this->assertEquals($expectedResult, $result);
    }
}
