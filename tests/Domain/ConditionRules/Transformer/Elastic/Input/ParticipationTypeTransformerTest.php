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
use Proximum\Vimeet\Domain\ConditionRules\GetSheetIdsByParticipationTypeIds;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\ParticipationTypeTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class ParticipationTypeTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $field = new Field('participation_type', new ComparisonOperatorIn(), 'select', [1, 2]);

        $getSheetIdsByParticipationTypeIds = $this->prophesize(GetSheetIdsByParticipationTypeIds::class);
        $getSheetIdsByParticipationTypeIds->__invoke([1, 2])
            ->shouldBeCalled()
            ->willReturn([123, 456]);

        $taggedNomenclatureTransformer = new ParticipationTypeTransformer($getSheetIdsByParticipationTypeIds->reveal());
        $result = $taggedNomenclatureTransformer->transform($field);

        $expectedResult = [
            'nested' => [
                'path' => 'sheets',
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'match' => [
                                    'sheets.id' => 123
                                ]
                            ],
                            [
                                'match' => [
                                    'sheets.id' => 456
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
