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
use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;
use Proximum\Vimeet\Domain\Catalog\View\NomenclatureFilterView;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TaggedNomenclatureTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TextTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEndsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\Model\Event;

class TaggedNomenclatureTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $nomenclatureFilterViews = [
            1 => new NomenclatureFilterView(1, 'mbappe', ['u58b57c0ecbdb3' => 'dribble'], [0 => 'tag', 1 => 'tag2']),
        ];

        $event = $this->prophesize(Event::class);
        $taggedNomenclatureFilterGetter = $this->prophesize(TaggedNomenclatureFilterGetter::class);
        $taggedNomenclatureFilterGetter->getNomenclaturesItemsByEvent($event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($nomenclatureFilterViews);

        $taggedNomenclatureTransformer = new TaggedNomenclatureTransformer($taggedNomenclatureFilterGetter->reveal());
        $taggedNomenclatureTransformer->setEventAndLocale($event->reveal(), 'fr');
        $result = $taggedNomenclatureTransformer->transform(
            new Field('nestedTaggedData.1', new ComparisonOperatorIn(), 'checkbox', ['57eced1b99305', '57eced1b994ef'])
        );

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
