<?php

namespace Proximum\Vimeet\Tests\Application\Components\Spot;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Spot\SpotImporter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Spot\Import;
use Proximum\Vimeet\Domain\View\Spot\Import\SheetView;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Infrastructure\Adapter\ValidatorAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\SerializerAdapter;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SpotImporterTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $validatorAdapter;

    /** @var ObjectProphecy */
    private $translatorAdapter;

    /*** @var ObjectProphecy */
    private $serializerAdapter;

    /** @var ObjectProphecy */
    private $importedFile;

    /** @var Event */
    private $event;

    /** @var string */
    private $locale;

    /** @var string */
    private $path;

    public function setUp()
    {
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->validatorAdapter = $this->prophesize(ValidatorAdapter::class);
        $this->translatorAdapter = $this->prophesize(TranslatorAdapter::class);
        $this->serializerAdapter = $this->prophesize(SerializerAdapter::class);
        $this->importedFile = $this->prophesize(File::class);
        $this->event = EventFactory::createEvent();
        $this->locale = 'fr';
        $this->path =  __DIR__ . '/import_spot.csv';
    }

    public function testImport()
    {
        $sheetView1 = new SheetView(16938, 'title1');
        $sheetView2 = new SheetView(16931, 'title2');
        $sheetView3 = new SheetView(16919, 'title3');
        $sheetView4 = new SheetView(16566, 'title4');
        $sheetView5 = new SheetView(16565, 'title6');
        $sheetView6 = new SheetView(16562, 'title7');

        // Mock of file content
        $sheetIds1 = [16938, 16931, 16919];
        $sheetIds2 = [16566, 12];
        $sheetIds3 = [16565];
        $sheetIds4 = [16562];

        $expectedImportedDenormalizedSpot1 = new Import(new Spot('A1', $this->event, 10, 10, 10, 1, 1, 0), $sheetIds1);
        $expectedImportedDenormalizedSpot2 = new Import(new Spot('A2', $this->event, '10', '2', '33', '1', '4', '1'), $sheetIds2);
        $expectedImportedDenormalizedSpot3 = new Import(new Spot('A3', $this->event, '10', '2', '33', '1', '4', '1'), $sheetIds3);
        $expectedImportedDenormalizedSpot4 = new Import(new Spot('A1', $this->event, '10', '2', '33', '1', '4', '1'), $sheetIds4);
        $expectedImportedDenormalizedSpot5 = new Import(new Spot('a3', $this->event, '10', '2', '33', '1', '4', '1'), []);

        $expectedDenormalizedResults = [
            $expectedImportedDenormalizedSpot1,
            $expectedImportedDenormalizedSpot2,
            $expectedImportedDenormalizedSpot3,
            $expectedImportedDenormalizedSpot4,
            $expectedImportedDenormalizedSpot5,
        ];

        $this
            ->serializerAdapter
            ->deserialize(Argument::type('string'), Import::class, 'csv', [
                'csv_delimiter' => ';',
                'event' => $this->event,
            ])
            ->shouldBeCalled()
            ->willReturn($expectedDenormalizedResults);

        $this->validatorAdapter
            ->validate($expectedImportedDenormalizedSpot1->spot)
            ->shouldBeCalled()
            ->willReturn([]);

        $this->sheetRepository->getSheetViewByEventAndId($this->event, 16938)->shouldBeCalled()->willReturn($sheetView1);
        $this->sheetRepository->getSheetViewByEventAndId($this->event, 16931)->shouldBeCalled()->willReturn($sheetView2);
        $this->sheetRepository->getSheetViewByEventAndId($this->event, 16919)->shouldBeCalled()->willReturn($sheetView3);

        $this->validatorAdapter
            ->validate($expectedImportedDenormalizedSpot2->spot)
            ->shouldBeCalled()
            ->willReturn([]);

        $this->sheetRepository->getSheetViewByEventAndId($this->event, 16566)->shouldBeCalled()->willReturn($sheetView4);

        $this->translatorAdapter
            ->trans('validators.spot.reference.affected', [], 'validators', 'fr')
            ->shouldBeCalledTimes(2)
            ->willReturn('validators.spot.reference.affected');

        $this->sheetRepository->getSheetViewByEventAndId($this->event, 12)->shouldBeCalled()->willReturn(null);

        $this->translatorAdapter->trans(
            'validators.spot.sheet.not_exist',
            ['%sheetId%' => 12],
            'validators',
            'fr'
        )->shouldBeCalled()
            ->willReturn('validators.spot.sheet.not_exist');

        $this->validatorAdapter
            ->validate($expectedImportedDenormalizedSpot3->spot)
            ->shouldBeCalled()
            ->willReturn([]);

        $this->sheetRepository->getSheetViewByEventAndId($this->event, 16565)->shouldBeCalled()->willReturn($sheetView5);

        $this->validatorAdapter
            ->validate($expectedImportedDenormalizedSpot4->spot)
            ->shouldBeCalled()
            ->willReturn([]);

        $this->validatorAdapter
            ->validate($expectedImportedDenormalizedSpot5->spot)
            ->shouldBeCalled()
            ->willReturn([]);

        $this->sheetRepository->getSheetViewByEventAndId($this->event, 16562)->shouldBeCalled()->willReturn($sheetView6);

        $spotImporter = new SpotImporter(
            $this->sheetRepository->reveal(),
            $this->validatorAdapter->reveal(),
            $this->translatorAdapter->reveal(),
            $this->serializerAdapter->reveal(),
            $this->path
        );

        $result = $spotImporter->import($this->event, $this->importedFile->reveal(), $this->locale);

        $this->assertEquals($expectedDenormalizedResults, $result);
    }
}
