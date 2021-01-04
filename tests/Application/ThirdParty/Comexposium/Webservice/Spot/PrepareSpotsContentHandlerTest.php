<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\PrepareSpotsContent;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\PrepareSpotsContentHandler;

class PrepareSpotsContentHandlerTest extends TestCase
{
    public function testHandle()
    {
        $serializerAdapter = $this->prophesize(SerializerAdapterInterface::class);

        $serializerAdapter
            ->serialize(
                [
                    ['sheet_id' => 13, 'reference' => 'StandA', 'foo' => 'bar'],
                    ['sheet_id' => 27, 'reference' => 'StandB.1'],
                    ['sheet_id' => 27, 'reference' => 'StandB.2'],
                ],
                'csv',
                [
                    'charset' => Charset::WINDOWS_1252,
                    'csv_delimiter' => ';',
                ]
            )
            ->shouldBeCalled()
            ->willReturn(['whatever-data'])
        ;

        // data with 1 "stand"
        $rawData1 = new \stdClass();
        $rawData1->reference = 1337;
        $rawData1->stand = new \stdClass();
        $rawData1->stand->reference = 'StandA';
        $rawData1->stand->foo = 'bar';

        // data with 2 "stands"
        $rawData2 = new \stdClass();
        $rawData2->reference = 666;
        $spot1 = new \stdClass();
        $spot1->reference = 'StandB.1';
        $spot2 = new \stdClass();
        $spot2->reference = 'StandB.2';
        $rawData2->stand = [$spot1, $spot2];

        // data without "stand"
        $rawData3 = new \stdClass();
        $rawData3->reference = 777;

        $prepareSpotsContentHandler = new PrepareSpotsContentHandler($serializerAdapter->reveal());
        $result = $prepareSpotsContentHandler->handle(
            new PrepareSpotsContent(
                [
                    13 => $rawData1,
                    27 => $rawData2,
                    777 => $rawData3,
                ]
            )
        );

        $this->assertEquals(['whatever-data'], $result);
    }
}
