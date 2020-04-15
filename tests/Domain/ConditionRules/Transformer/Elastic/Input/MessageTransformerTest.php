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
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\MessageTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\Model\Event;

class MessageTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $field = new Field('messagesReceived', new ComparisonOperatorIn(), 'select', [1, 9], 'FR');
        $event = $this->prophesize(Event::class);

        $messageTransformer = new MessageTransformer();
        $messageTransformer->setEventAndLocale($event->reveal(), 'fr');

        $expectedResult = [
            'nested' => [
                'path' => 'messagesReceived',
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'match' => [
                                    'messagesReceived.id' => 1
                                ]
                            ],
                            [
                                'match' => [
                                    'messagesReceived.id' => 9
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertSame($messageTransformer->transform($field), $expectedResult);
    }
}
