<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\Spot;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\ValidatorInterface;
use Proximum\Vimeet\Application\Components\Spot\SpotImporter;
use Proximum\Vimeet\Application\Exception\Spot\Import\InvalidImportHeaderFileFormatException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Spot\Import;
use Proximum\Vimeet\Domain\View\Spot\Import\SheetView;
use Proximum\Vimeet\Domain\View\Spot\Import\SpotImportView;
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
        $this->path = '/fake/path';
    }

    public function testImport()
    {
        $sheetView1 = new SheetView(16938, 'title1');
        $sheetView2 = new SheetView(16931, 'title2');
        $sheetView3 = new SheetView(16919, 'title3');
        $sheetView4 = new SheetView(16566, 'title4');
        $sheetView5 = new SheetView(16565, 'title6');
        $sheetView6 = new SheetView(16562, 'title7');
        $sheetViewInexistent = new SheetView(12, 'validators.spot.sheet.not_exist');

        // Mock of file content
        $expectedSerialization = [
            ['reference', 'size', 'meetingCapacity', 'seatCapacity', 'active', 'priority', 'visio', 'sheets'],
            ['A1', '10', '2', '33', true, '4', false, '16938, 16931, 16919'],
            ['A2', '10', '2', '33', '1', '4', '1', '16566, 12'],
            ['A3', '10', '2', '33', '1', '4', '1', '16565'],
            ['A1', '10', '2', '33', '1', '4', '1', '16562'],
        ];

        $expectedImportedSpot1 = new Import('A1', '10', '2', '33', true, '4', false);
        $expectedImportedSpot2 = new Import('A2', '10', '2', '33', '1', '4', '1');
        $expectedImportedSpot3 = new Import('A3', '10', '2', '33', '1', '4', '1');
        $expectedImportedSpot4 = new Import('A1', '10', '2', '33', '1', '4', '1');

        $sheetViews1 = [
            16938 => $sheetView1,
            16931 => $sheetView2,
            16919 => $sheetView3,
        ];
        $sheetViews2 = [
            16566 => $sheetView4,
            12    => $sheetViewInexistent,
        ];
        $sheetViews3 = [
            16565 => $sheetView5,
        ];
        $sheetViews4 = [
            16562 => $sheetView6,
        ];

        $errorMessage1 = [];
        $errorMessage2 = [];
        $errorMessage3 = [];
        $errorMessage4 = ['reference' => 'validators.spot.reference.affected'];

        $expectedResults = [
            new SpotImportView($expectedImportedSpot1, $sheetViews1, $errorMessage1),
            new SpotImportView($expectedImportedSpot2, $sheetViews2, $errorMessage2),
            new SpotImportView($expectedImportedSpot3, $sheetViews3, $errorMessage3),
            new SpotImportView($expectedImportedSpot4, $sheetViews4, $errorMessage4),
        ];

        $this
            ->serializerAdapter
            ->serialize($this->path, 'csv')
            ->shouldBeCalled()
            ->willReturn($expectedSerialization);

        $this->validatorAdapter
            ->validate(
                $expectedImportedSpot1,
                ValidatorInterface::VALIDATOR_SPOT_IMPORT_TYPE
            )
            ->shouldBeCalled()
            ->willReturn([]);

        $this->sheetRepository->getSheetViewsByEventById($this->event, 16938)->shouldBeCalled()->willReturn($sheetView1);
        $this->sheetRepository->getSheetViewsByEventById($this->event, 16931)->shouldBeCalled()->willReturn($sheetView2);
        $this->sheetRepository->getSheetViewsByEventById($this->event, 16919)->shouldBeCalled()->willReturn($sheetView3);

        $this->validatorAdapter
            ->validate(
                $expectedImportedSpot2,
                ValidatorInterface::VALIDATOR_SPOT_IMPORT_TYPE
            )
            ->shouldBeCalled()
            ->willReturn([]);

        $this->sheetRepository->getSheetViewsByEventById($this->event, 16566)->shouldBeCalled()->willReturn($sheetView4);

        $this->translatorAdapter
            ->trans("validators.spot.reference.affected", [], "validators", "fr")
            ->shouldBeCalled()
            ->willReturn('validators.spot.reference.affected');

        $this->sheetRepository->getSheetViewsByEventById($this->event, 12)->shouldBeCalled()->willReturn(null);

        $this->translatorAdapter->trans(
            'validators.spot.sheet.not_exist',
            [],
            'validators',
            'fr'
        )->shouldBeCalled()
        ->willReturn('validators.spot.sheet.not_exist');

        $this->validatorAdapter
            ->validate(
                $expectedImportedSpot3,
                ValidatorInterface::VALIDATOR_SPOT_IMPORT_TYPE
            )
            ->shouldBeCalled()
            ->willReturn([]);

        $this->sheetRepository->getSheetViewsByEventById($this->event, 16565)->shouldBeCalled()->willReturn($sheetView5);

        $this->validatorAdapter
            ->validate(
                $expectedImportedSpot4,
                ValidatorInterface::VALIDATOR_SPOT_IMPORT_TYPE
            )
            ->shouldBeCalled()
            ->willReturn([]);

        $this->sheetRepository->getSheetViewsByEventById($this->event, 16562)->shouldBeCalled()->willReturn($sheetView6);

        $spotImporter = new SpotImporter(
            $this->sheetRepository->reveal(),
            $this->validatorAdapter->reveal(),
            $this->translatorAdapter->reveal(),
            $this->serializerAdapter->reveal(),
            $this->path
        );

        $result = $spotImporter->import($this->event, $this->importedFile->reveal(), $this->locale);

        $this->assertEquals($expectedResults, $result);
    }

    public function testInvalidCsvHeader()
    {
        // Mock of file content
        $expectedSerialization = [0 => ['wrong headers']];

        $this->expectException(InvalidImportHeaderFileFormatException::class);

        $this
            ->serializerAdapter
            ->serialize($this->path, 'csv')
            ->shouldBeCalled()
            ->willReturn($expectedSerialization);

        $spotImporter = new SpotImporter(
            $this->sheetRepository->reveal(),
            $this->validatorAdapter->reveal(),
            $this->translatorAdapter->reveal(),
            $this->serializerAdapter->reveal(),
            $this->path
        );

        $result = $spotImporter->import($this->event, $this->importedFile->reveal(), $this->locale);
    }
}
