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
use Proximum\Vimeet\Domain\ConditionRules\GetTagsByNomenclature;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TaggedNomenclatureTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\Model\Event;

class TaggedNomenclatureTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $event = $this->prophesize(Event::class);
        $field = new Field('nestedTaggedData.1', new ComparisonOperatorIn(), 'checkbox', ['57eced1b99305', '57eced1b994ef'], 'fr');
        $getTagsByNomenclature = $this->prophesize(GetTagsByNomenclature::class);
        $getTagsByNomenclature->__invoke($field, $event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([0 => 'tag', 1 => 'tag2']);

        $taggedNomenclatureTransformer = new TaggedNomenclatureTransformer($getTagsByNomenclature->reveal());
        $taggedNomenclatureTransformer->setEventAndLocale($event->reveal(), 'fr');
        $result = $taggedNomenclatureTransformer->transform($field);

        $expectedResult = [
            [
                'nested' => [
                    'path' => 'nestedTaggedData',
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'term' => [
                                        'nestedTaggedData.tag' => [
                                            'value' => 'tag',
                                        ]
                                    ]
                                ],
                                [
                                    'term' => [
                                        'nestedTaggedData.tag' => [
                                            'value' => 'tag2'
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
