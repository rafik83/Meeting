<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Get\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\CustomDataConverter;

class CustomDataConverterTest extends TestCase
{
    public function testConvert()
    {
        $customDataMapping = [
            'tags' => [
                'sheet_organization_staff' => 'ZL_Effectif',
                'sheet_generic_tag_20' => 'ZL_ACTIVITE',
                'sheet_generic_tag_10' => 'ZL_TypePrestation',
            ],
        ];

        $data = [
            'ZL_Effectif' => 'A1',
            'ZL_Whatever' => 'whatever',
            'ZL_TypePrestation' => [
                'P12',
                'P3',
                'P5',
            ],
        ];

        $expectedResult = [
            'tags' => [
                'sheet_organization_staff' => 'A1',
                'sheet_generic_tag_10' => [
                    'P12',
                    'P3',
                    'P5',
                ],
            ],
        ];

        $customDataConverter = new CustomDataConverter();
        $result = $customDataConverter->convert($customDataMapping, $data);

        $this->assertEquals($expectedResult, $result);
    }
}
