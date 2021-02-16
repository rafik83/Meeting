<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Save\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter\CustomDataConverter;

class CustomDataConverterTest extends TestCase
{
    public function testConvert()
    {
        $customDataMapping = [
            'tags' => [
                'sheet_organization_staff' => 'ZL_Effectif',
                'sheet_generic_tag_10' => 'ZL_TypePrestation',
                'sheet_generic_tag_20' => 'ZL_ACTIVITE',
            ],
        ];

        $taggedData = [
            'tags' => [
                'sheet_organization_staff' => 'A1',
                'sheet_generic_tag_10' => [
                    'P12',
                    'P3',
                    'P5',
                ],
            ],
        ];

        $expectedResult = [
            'ZL_Effectif' => 'A1',
            'ZL_TypePrestation' => [
                'P12',
                'P3',
                'P5',
            ],
        ];

        $customDataConverter = new CustomDataConverter();
        $result = $customDataConverter->convert($customDataMapping, $taggedData);

        $this->assertEquals($expectedResult, $result);
    }

    public function testConvertWithProduct()
    {
        $customDataMapping = [
            'tags' => [
                'sheet_organization_staff' => 'ZL_Effectif',
                'sheet_generic_tag_10' => 'ZL_TypePrestation',
                'sheet_generic_tag_20' => 'ZL_ACTIVITE',
            ],
            'products' => [
                1 => 'ZL_PRODUCT_1',
                2 => 'ZL_PRODUCT_2',
                3 => 'ZL_PRODUCT_3',
            ]
        ];

        $taggedData = [
            'tags' => [
                'sheet_organization_staff' => 'A1',
                'sheet_generic_tag_10' => [
                    'P12',
                    'P3',
                    'P5',
                ],
            ],
            'products' => [
                1 => true,
            ],
        ];

        $expectedResult = [
            'ZL_Effectif' => 'A1',
            'ZL_TypePrestation' => [
                'P12',
                'P3',
                'P5',
            ],
            'ZL_PRODUCT_1' => 'True',
            'ZL_PRODUCT_2' => 'False',
            'ZL_PRODUCT_3' => 'False',
        ];

        $customDataConverter = new CustomDataConverter();
        $result = $customDataConverter->convert($customDataMapping, $taggedData);

        $this->assertEquals($expectedResult, $result);
    }
}
