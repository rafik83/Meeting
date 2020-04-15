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
use Prophecy\Argument;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\ConditionRulesToElasticTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\KeywordTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\MessageTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\NullableTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\ParticipationTypeTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\RadioTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TaggedNomenclatureTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TemplateObjectFilterTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TextTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorBeginsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotNull;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorAnd;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorOr;
use Proximum\Vimeet\Domain\Model\Event;

class ConditionRulesToElasticTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $event = $this->prophesize(Event::class);

        $condition = new Condition(
            $event->reveal(),
            'fr',
            new LogicalOperatorAnd,
            [
                new Field('spotReference', new ComparisonOperatorEqual(), 'text', 'A1', 'fr'),
                new Condition(
                    $event->reveal(),
                    'fr',
                    new LogicalOperatorOr,
                    [
                        new Field('sheetName', new ComparisonOperatorContains(), 'text', 'S1', 'fr'),
                        new Field('spotReference', new ComparisonOperatorBeginsWith(), 'text', 'U', 'fr'),
                        new Condition(
                            $event->reveal(),
                            'fr',
                            new LogicalOperatorAnd,
                            [
                                new Field('lastName', new ComparisonOperatorNotContains(), 'text', 'test', 'fr'),
                                new Field('firstName', new ComparisonOperatorNotNull(), 'text', 'mathieu', 'fr'),
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
                        'match' => [
                            'spotReference' => 'A1',
                        ],
                    ],
                    [
                        'bool' => [
                            'should' => [
                                [
                                    'query_string' => [
                                        'default_field' => 'sheetName',
                                        'query' => '*S1*',
                                        'default_operator' => 'AND',
                                    ],
                                ],
                                [
                                    'query_string' => [
                                        'default_field' => 'spotReference',
                                        'query' => 'U*',
                                        'default_operator' => 'AND',
                                    ],
                                ],
                                [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'bool' => [
                                                    'must_not' => [
                                                        'query_string' => [
                                                            'default_field' => 'lastName',
                                                            'query' => '*test*',
                                                            'default_operator' => 'AND',
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
                                                                    'field' => 'firstName',
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

        $nullableTransformer = new NullableTransformer();
        $taggedNomenclatureTransformer = $this->prophesize(TaggedNomenclatureTransformer::class);
        $taggedNomenclatureTransformer->supports(Argument::any())->shouldBeCalled()->willReturn(false);
        $radioTransformer = new RadioTransformer();
        $textTransformer = new TextTransformer();
        $participationTypeTransformer = $this->prophesize(ParticipationTypeTransformer::class);
        $participationTypeTransformer->supports(Argument::any())->shouldBeCalled()->willReturn(false);
        $templateObjectFilterTransformer = $this->prophesize(TemplateObjectFilterTransformer::class);
        $templateObjectFilterTransformer->supports(Argument::any())->shouldBeCalled()->willReturn(false);
        $messageTransform = $this->prophesize(MessageTransformer::class);
        $messageTransform->supports(Argument::any())->shouldBeCalled()->willReturn(false);
        $keywordsTransform = $this->prophesize(KeywordTransformer::class);
        $keywordsTransform->supports(Argument::any())->shouldBeCalled()->willReturn(false);

        $transformer = new ConditionRulesToElasticTransformer(
            $nullableTransformer,
            $radioTransformer,
            $taggedNomenclatureTransformer->reveal(),
            $textTransformer,
            $participationTypeTransformer->reveal(),
            $templateObjectFilterTransformer->reveal(),
            $messageTransform->reveal(),
            $keywordsTransform->reveal()
        );
        $result = $transformer->transform($condition);

        $this->assertSame($expectedResult, $result);
    }
}
