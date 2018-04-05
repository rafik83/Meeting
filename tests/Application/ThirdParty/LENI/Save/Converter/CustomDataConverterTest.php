<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Save\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter\CustomDataConverter;

class CustomDataConverterTest extends TestCase
{
    public function testConvert()
    {
        $customDataMapping = [
            'sheet_organization_staff' => 'ZL_Effectif',
            'sheet_generic_tag_10' => 'ZL_TypePrestation',
            'sheet_generic_tag_20' => 'ZL_ACTIVITE',
        ];

        $taggedData = [
            'sheet_organization_staff' => 'A1',
            'sheet_generic_tag_10' => [
                'P12',
                'P3',
                'P5',
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
}
