<?php

namespace Proximum\Vimeet\Tests\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TemplateObjectFilterTransformer;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class TemplateObjectFilterTransformerTest extends TestCase
{
    public function testTransformNotSupport(): void
    {
        $field = new Field('status', new ComparisonOperatorIn(), 'checkbox', ['none', 'yes'], 'fr');

        $templateObjectFilterTransformer = new TemplateObjectFilterTransformer();
        $result = $templateObjectFilterTransformer->transform($field);

        $expectedResult = [];

        $this->assertSame($result, $expectedResult);
    }

    public function testTransformCheckbox(): void
    {
        $field = new Field('templateObjectFilters.57eced1b99305', new ComparisonOperatorIn(), 'checkbox', ['none', 'yes'], 'fr');

        $templateObjectFilterTransformer = new TemplateObjectFilterTransformer();
        $result = $templateObjectFilterTransformer->transform($field);

        $expectedResult = [
            'nested' => [
                'path' => 'templateObjectFilters',
                'query' => [
                    [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    'templateObjectFilters.key' => '57eced1b99305',
                                ],
                            ],
                        ],
                    ],
                    [
                        'bool' => [
                            'should' => [
                                [
                                    'match' => [
                                        'templateObjectFilters.value' => 'none',
                                    ],
                                ],
                                [
                                    'match' => [
                                        'templateObjectFilters.value' => 'yes',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($result, $expectedResult);
    }

    public function testTransformEmptyCheckbox(): void
    {
        $field = new Field('templateObjectFilters.57eced1b99305', new ComparisonOperatorIn(), 'checkbox', [], 'fr');

        $templateObjectFilterTransformer = new TemplateObjectFilterTransformer();
        $result = $templateObjectFilterTransformer->transform($field);

        $expectedResult = [
            'nested' => [
                'path' => 'templateObjectFilters',
                'query' => [],
            ],
        ];

        $this->assertSame($result, $expectedResult);
    }

    public function testTransformRadioFilled(): void
    {
        $field = new Field('templateObjectFilters.57eced1b99305', new ComparisonOperatorEqual(), 'radio', true, 'fr');

        $templateObjectFilterTransformer = new TemplateObjectFilterTransformer();
        $result = $templateObjectFilterTransformer->transform($field);

        $expectedResult = [
            'nested' => [
                'path' => 'templateObjectFilters',
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                'templateObjectFilters.key' => '57eced1b99305',
                            ]
                        ],
                        'must_not' => [
                            'match' => [
                                'templateObjectFilters.value' => 'none',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($result, $expectedResult);
    }

    public function testTransformRadioNotFilled(): void
    {
        $field = new Field('templateObjectFilters.57eced1b99305', new ComparisonOperatorEqual(), 'radio', false, 'fr');

        $templateObjectFilterTransformer = new TemplateObjectFilterTransformer();
        $result = $templateObjectFilterTransformer->transform($field);

        $expectedResult = [
            'nested' => [
                'path' => 'templateObjectFilters',
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'match' => [
                                    'templateObjectFilters.key' => '57eced1b99305',
                                ],
                            ],
                            [
                                'match' => [
                                    'templateObjectFilters.value' => 'none',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($result, $expectedResult);
    }
}
