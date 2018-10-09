<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\ConditionRules\Transformer\Elastic\Input;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TextTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEndsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotContains;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class TextTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $result1 = TextTransformer::transform(
            new Field('LastName', new ComparisonOperatorNotContains(), 'text', 'marchois')
        );

        $expectedResult1 = [
            'bool' => [
                'must_not' => [
                    'query_string' => [
                        'default_field' => 'LastName',
                        'query' => '*marchois*',
                        'default_operator' => 'AND',
                    ]
                ]
            ]
        ];

        $result2 = TextTransformer::transform(
            new Field('participants.firstName', new ComparisonOperatorEndsWith(), 'text', 'hieu')
        );

        $expectedResult2 = [
            'nested' => [
                'path' => 'participants',
                'query' => [
                    'query_string' => [
                        'default_field' => 'participants.firstName',
                        'query' => '*hieu',
                        'default_operator' => 'AND'
                    ]
                ]
            ]
        ];

        $this->assertSame($result1, $expectedResult1);
        $this->assertSame($result2, $expectedResult2);
    }
}
