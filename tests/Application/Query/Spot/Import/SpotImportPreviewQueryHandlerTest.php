<?php

namespace Proximum\Vimeet\Application\Query\Spot\Import;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Spot\SpotImporter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Spot\Import;
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

        $expectedImportedSpot1 = new Import(new Spot('A1', $this->event, '10', '2', '33', true, '4', false), [1, 2]);
        $expectedImportedSpot2 = new Import(new Spot('A2', $this->event, '10', '2', '33', '1', '4', '1'), [3, 4]);
        $expectedImportedSpot3 = new Import(new Spot('A3', $this->event, '10', '2', '33', '1', '4', '1'), [3]);
        $expectedImportedSpot3->errorMessages = ['La fiche ayant l\'identifiant 3 a déjà été attribuée à un lieu'];
        $expectedImportedSpot4 = new Import(new Spot('A1', $this->event, '10', '2', '33', '1', '4', '1'), [5, 6]);
        $expectedImportedSpot1->errorMessages = ['Cette référence existe déjà'];

        $expectedResults = [$expectedImportedSpot1, $expectedImportedSpot2, $expectedImportedSpot3, $expectedImportedSpot4];

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
        $result = $handler->handle($spotImportPreviewQuery);

        $this->assertEquals($expectedResults, $result);
    }
}
