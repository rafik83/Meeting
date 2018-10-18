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
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TaggedNomenclatureTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TextTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEndsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class TaggedNomenclatureTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $taggedNomenclatureTransformer = new TaggedNomenclatureTransformer();
        $result = $taggedNomenclatureTransformer->transform(
            new Field('nestedTaggedData.273', new ComparisonOperatorIn(), 'checkbox', ['57eced1b99305', '57eced1b994ef'])
        );

        $expectedResult = [
            [
                'nested' => [
                    'path' => 'nestedTaggedData',
                    'query' => [
                        'bool' => [
                            'should' => [
                                [
                                    'term' => [
                                        'nestedTaggedData.tag' => [
                                            'value' => 'sheet_test',
                                        ]
                                    ]
                                ],
                                [
                                    'term' => [
                                        'nestedTaggedData.tag' => [
                                            'value' => 'sheet_test2'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'nested' => [
                    'path' => 'nestedTaggedData.values',
                    'query' => [
                        'bool' => [
                            'should' => [
                                [
                                    'term' => [
                                        'nestedTaggedData.values.value' => [
                                            'value' => '57eced1b99305'
                                        ]
                                    ]
                                ],
                                [
                                    'term' => [
                                        'nestedTaggedData.values.value' => [
                                            'value' => '57eced1b994ef'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertSame($result, $expectedResult);
    }
}
