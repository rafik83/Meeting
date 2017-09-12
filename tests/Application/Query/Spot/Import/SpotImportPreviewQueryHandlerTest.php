<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Spot\Import;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Spot\SpotImporter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Spot\Import;
use Proximum\Vimeet\Domain\View\Spot\Import\SheetView;
use Proximum\Vimeet\Domain\View\Spot\Import\SpotImportView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SpotImportPreviewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $spotImporter;

    /** @var ObjectProphecy */
    private $spotImportPreviewQuery;

    /** @var Event */
    private $event;

    public function setUp()
    {
        $this->spotImporter = $this->prophesize(SpotImporter::class);
        $this->spotImportPreviewQuery = $this->prophesize(SpotImportPreviewQuery::class);
        $this->event = EventFactory::createEvent();
    }

    public function testHandle()
    {
        $dateTime = new \DateTime();
        $file = new File('/path', $dateTime);

        $spotImportPreviewQuery = new SpotImportPreviewQuery($this->event, $file, 'fr');

        $sheetView1 = new SheetView(16938, 'title1');
        $sheetView2 = new SheetView(16931, 'title2');
        $sheetView3 = new SheetView(16919, 'title3');
        $sheetView4 = new SheetView(16566, 'title4');
        $sheetView5 = new SheetView(16565, 'title6');
        $sheetView6 = new SheetView(16562, 'title7');
        $sheetViewInexistent = new SheetView(12, 'validators.spot.sheet.not_exist');

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
            ->spotImporter
            ->import(
                $spotImportPreviewQuery->event,
                $spotImportPreviewQuery->importedSpotFileName,
                $spotImportPreviewQuery->locale
            )
            ->shouldBeCalled()
            ->willReturn($expectedResults);

        $handler = new SpotImportPreviewQueryHandler($this->spotImporter->reveal());
        $resultViews = $handler->handle($spotImportPreviewQuery);

        $this->assertEquals($expectedResults, $resultViews);
    }
}
