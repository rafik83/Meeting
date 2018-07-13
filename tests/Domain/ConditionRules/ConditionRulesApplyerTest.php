<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\ConditionRules;

use Proximum\Vimeet\Domain\ConditionRules\ConditionRulesApplyer;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorAnd;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorOr;

class ConditionRulesApplyerTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testApply(array $data, bool $expectedResult)
    {
        $rule = new Condition(
            new LogicalOperatorAnd(),
            [
                new Field('Activity', new ComparisonOperatorEqual, 'A1'),
                new Condition(
                    new LogicalOperatorOr,
                    [
                        new Field('Sector', new ComparisonOperatorIn, ['S1', 'S3']),
                        new Field('Universe', new ComparisonOperatorEqual, 'U4'),
                    ]
                ),
            ]
        );

        $this->assertEquals($expectedResult, ConditionRulesApplyer::apply($rule, $data));
    }

    public function dataProvider()
    {
        return [
            [['Activity' => 'A1', 'Sector' => 'S1', 'Universe' => 'U4'], true],
            [['Activity' => 'A1', 'Sector' => 'S1', 'Universe' => 'U3'], true],
            [['Activity' => 'A1', 'Sector' => 'S3', 'Universe' => 'U3'], true],
            [['Activity' => 'A1', 'Sector' => 'S2', 'Universe' => 'U4'], true],
            [['Activity' => 'A1', 'Sector' => 'S2', 'Universe' => 'U2'], false],
            [['Activity' => 'A2', 'Sector' => 'S1', 'Universe' => 'U4'], false],
            [['Activity' => 'A1', 'Sector' => ['S1'], 'Universe' => 'U4'], true],
            [['Activity' => ['A1'], 'Sector' => ['S1'], 'Universe' => 'U4'], true],
        ];
    }
}
