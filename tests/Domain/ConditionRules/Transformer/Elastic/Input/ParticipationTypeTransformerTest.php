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
use Proximum\Vimeet\Domain\ConditionRules\GetSheetIdsByFilters;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\ParticipationTypeTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\Model\Event;

class ParticipationTypeTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $field = new Field('participation_type', new ComparisonOperatorIn(), 'select', [1, 2], 'fr');
        $event = $this->prophesize(Event::class);

        $getSheetIdsByFilters = $this->prophesize(GetSheetIdsByFilters::class);
        $getSheetIdsByFilters->__invoke($event->reveal(), 'fr', ['type' => [1, 2]])
            ->shouldBeCalled()
            ->willReturn([123, 456]);

        $participationTypeTransformer = new ParticipationTypeTransformer($getSheetIdsByFilters->reveal());
        $participationTypeTransformer->setEventAndLocale($event->reveal(), 'fr');

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

        $this->assertSame($participationTypeTransformer->transform($field), $expectedResult);
    }
}
