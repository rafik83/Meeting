<?php

namespace Proximum\Vimeet\Tests\Application\Components\Spot\Denormalizer;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Spot\Denormalizer\SpotDenormalizer;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Spot\Import;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SpotDenormalizerTest extends TestCase
{
    /** @var Event */
    private $event;

    /** @var SpotDenormalizer */
    private $spotDenormalizer;

    /** @var ObjectProphecy */
    private $translatorAdapter;

    public function setUp()
    {
        $this->event = EventFactory::createEvent();
        $this->translatorAdapter = $this->prophesize(TranslatorAdapter::class);
        $this->spotDenormalizer = new SpotDenormalizer($this->translatorAdapter->reveal());
    }

    public function testHandle()
    {
        // Mock of file content
        $inputDatas = [
            [
                'reference' => 'A1',
                'size' => 10,
                'meetingCapacity' => 10,
                'seatCapacity' => 10,
                'active' => 1,
                'priority' => 1,
                'visio' => 0,
                'sheets' => '16938, 16931',
            ],
            [
                'reference' => 'A2',
                'size' => 10,
                'meetingCapacity' => 2,
                'seatCapacity' => 33,
                'active' => 1,
                'priority' => 4,
                'visio' => 1,
                'sheets' => '16923, 16905, 16931',
            ],
            [
                'reference' => 'A3',
                'size' => 10,
                'meetingCapacity' => 2,
                'seatCapacity' => 33,
                'active' => 1,
                'priority' => 4,
                'visio' => 1,
                'sheets' => '16888',
            ],
            [
                'reference' => 'A1',
                'size' => 10,
                'meetingCapacity' => 2,
                'seatCapacity' => 33,
                'active' => 1,
                'priority' => 4,
                'visio' => 1,
                'sheets' => '1000',
            ],
        ];

        // Mock of file content
        $sheetIds1 = [16938, 16931];
        $sheetIds2 = [16923, 16905, 16931];
        $sheetIds3 = [16888];
        $sheetIds4 = [1000];

        $expectedImportedDenormalizedSpot1 = new Import(new Spot('A1', $this->event, 10, 10, 10, 1, 1, 0), $sheetIds1);
        $expectedImportedDenormalizedSpot2 = new Import(new Spot('A2', $this->event, '10', '2', '33', '1', '4', '1'), $sheetIds2);
        $expectedImportedDenormalizedSpot3 = new Import(new Spot('A3', $this->event, '10', '2', '33', '1', '4', '1'), $sheetIds3);
        $expectedImportedDenormalizedSpot4 = new Import(new Spot('A1', $this->event, '10', '2', '33', '1', '4', '1'), $sheetIds4);

        $expectedResult = [
            $expectedImportedDenormalizedSpot1,
            $expectedImportedDenormalizedSpot2,
            $expectedImportedDenormalizedSpot3,
            $expectedImportedDenormalizedSpot4,
        ];

        $result = $this
            ->spotDenormalizer
            ->denormalize(
                $inputDatas,
                Import::class,
                'csv',
                [
                    'csv_delimiter' => ';',
                    'event' => $this->event,
                ]
            )
        ;

        $this->assertEquals($expectedResult, $result);
    }
}
