<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Get\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\CustomDataConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\DataConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\MainDataConverter;

class DataConverterTest extends TestCase
{
    public function testConvert()
    {
        $rawData = ['Prenom' => 'Bruce', 'Nom' => 'Willis', 'ZL_Effectif' => 'A1', 'ZL_FONCTION' => 'F1'];
        $customDataMapping = [
            'tags' => [
                'sheet_organization_staff' => 'ZL_Effectif',
                'participant_position' => 'ZL_FONCTION'
            ]
        ];

        $expectedResult = [
            'participant_firstname' => 'Bruce',
            'participant_lastname' => 'Willis',
            'sheet_organization_staff' => 'A1',
            'participant_position' => 'F1',
        ];

        $customDataConverter = $this->prophesize(CustomDataConverter::class);
        $customDataConverter
            ->convert($customDataMapping, $rawData)
            ->shouldBeCalled()
            ->willReturn(['sheet_organization_staff' => 'A1', 'participant_position' => 'F1']);

        $mainDataConverter = $this->prophesize(MainDataConverter::class);
        $mainDataConverter
            ->convert($rawData)
            ->shouldBeCalled()
            ->willReturn(
                [
                    'participant_firstname' => 'Bruce',
                    'participant_lastname' => 'Willis',
                    'participant_position' => 'F1',
                    'sheet_organization_staff' => 'A1',
                ]
            );

        $dataConverter = new DataConverter($mainDataConverter->reveal(), $customDataConverter->reveal());
        $this->assertEquals($expectedResult, $dataConverter->convert($customDataMapping, $rawData));
    }
}
