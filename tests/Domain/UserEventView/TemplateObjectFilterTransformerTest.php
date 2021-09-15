<?php

namespace Proximum\Vimeet\Tests\Domain\UserEventView;

use Proximum\Vimeet\Domain\Model\Filter\BooleanTemplateFilter;
use Proximum\Vimeet\Domain\Model\Filter\FilledTemplateFilter;
use Proximum\Vimeet\Domain\UserEventView\TemplateObjectFilterTransformer;
use PHPUnit\Framework\TestCase;

class TemplateObjectFilterTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $booleanTemplateFilter = $this->prophesize(BooleanTemplateFilter::class);
        $filledTemplateFilter = $this->prophesize(FilledTemplateFilter::class);

        $templateFilters = [
            'Maa89Mdc51' => $filledTemplateFilter->reveal(),
            'Mc459M863e' => $booleanTemplateFilter->reveal(),
        ];

        $formData = [
            'Maa89Mdc51' => [
                'path' => '/path/to/my/file'
            ],
            '4b674gba' => [
                'gender' => 'woman'
            ],
        ];

        $expectedResult = [
            [
                'type' => 'upload',
                'value' => '/path/to/my/file',
                'key' => 'Maa89Mdc51',
            ],
            [
                'type' => 'boolean',
                'value' => 'none',
                'key' => 'Mc459M863e',
            ]
        ];

        $result = TemplateObjectFilterTransformer::transform($templateFilters, $formData);

        $this->assertSame($result, $expectedResult);
    }
}
