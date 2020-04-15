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
        $textTransformer = new TextTransformer();

        $result1 = $textTransformer->transform(
            new Field('lastName', new ComparisonOperatorNotContains(), 'text', 'marchois', 'FR')
        );

        $expectedResult1 = [
            'bool' => [
                'must_not' => [
                    'query_string' => [
                        'default_field' => 'lastName',
                        'query' => '*marchois*',
                        'default_operator' => 'AND',
                    ]
                ]
            ]
        ];

        $result2 = $textTransformer->transform(
            new Field('participants.lastname', new ComparisonOperatorEndsWith(), 'text', 'hieu', 'FR')
        );

        $expectedResult2 = [
            'nested' => [
                'path' => 'participants',
                'query' => [
                    'query_string' => [
                        'default_field' => 'participants.lastname',
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
