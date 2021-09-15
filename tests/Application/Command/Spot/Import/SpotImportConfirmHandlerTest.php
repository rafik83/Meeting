<?php

namespace Proximum\Vimeet\Tests\Application\Command\Spot\Import;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Spot\Import\SpotImportConfirm;
use Proximum\Vimeet\Application\Command\Spot\Import\SpotImportConfirmHandler;
use Proximum\Vimeet\Application\Components\Spot\SpotImporter;
use Proximum\Vimeet\Application\View\Spot\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Domain\Spot\Import;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SpotFactory;

class SpotImportConfirmHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $spotImporter;

    /** @var ObjectProphecy */
    private $spotRepository;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var Event */
    private $event;

    /** @var File */
    private $filename;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function setUp()
    {
        $this->spotImporter = $this->prophesize(SpotImporter::class);
        $this->spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->event = EventFactory::createEvent();
        $this->dateTime = new \DateTime();
        $this->filename = new File(__DIR__ . 'import_spot.csv', $this->dateTime);
    }

    public function testHandle()
    {
        // Mock of file content
        $sheetIds1 = [16938];
        $sheetIds2 = [16566];
        $sheetIds3 = [16565];
        $sheetIds4 = [16562];

        $sheetIds = [16938, 16566, 16565, 16562];

        $sheet1 = SheetFactory::create($this->event);
        $sheetReflection = new \ReflectionClass($sheet1);
        $sheetIdReflection = $sheetReflection->getProperty('id');
        $sheetIdReflection->setAccessible(true);
        $sheetIdReflection->setValue($sheet1, 16938);

        $sheet2 = SheetFactory::create($this->event);
        $sheetReflection = new \ReflectionClass($sheet1);
        $sheetIdReflection = $sheetReflection->getProperty('id');
        $sheetIdReflection->setAccessible(true);
        $sheetIdReflection->setValue($sheet2, 16566);

        $sheet3 = SheetFactory::create($this->event);
        $sheetReflection = new \ReflectionClass($sheet1);
        $sheetIdReflection = $sheetReflection->getProperty('id');
        $sheetIdReflection->setAccessible(true);
        $sheetIdReflection->setValue($sheet3, 16565);

        $sheet4 = SheetFactory::create($this->event);
        $sheetReflection = new \ReflectionClass($sheet1);
        $sheetIdReflection = $sheetReflection->getProperty('id');
        $sheetIdReflection->setAccessible(true);
        $sheetIdReflection->setValue($sheet4, 16562);

        $expectedImportedDenormalizedSpot1 = new Import(new Spot('A1', $this->event, 10, 10, 10, 1, 1, 0), $sheetIds1);
        $expectedImportedDenormalizedSpot1->sheetViews = [new SheetView(16938, '')];
        $expectedImportedDenormalizedSpot2 = new Import(new Spot('A2', $this->event, '10', '2', '33', '1', '4', '1'), $sheetIds2);
        $expectedImportedDenormalizedSpot2->sheetViews = [new SheetView(16566, '')];
        $expectedImportedDenormalizedSpot3 = new Import(new Spot('A3', $this->event, '10', '2', '33', '1', '4', '1'), $sheetIds3);
        $expectedImportedDenormalizedSpot3->sheetViews = [new SheetView(16565, '')];
        $expectedImportedDenormalizedSpot4 = new Import(new Spot('A1', $this->event, '10', '2', '33', '1', '4', '1'), $sheetIds4);
        $expectedImportedDenormalizedSpot4->sheetViews = [new SheetView(16562, '')];

        $expectedSpotImport = [
            $expectedImportedDenormalizedSpot1,
            $expectedImportedDenormalizedSpot2,
            $expectedImportedDenormalizedSpot3,
            $expectedImportedDenormalizedSpot4,
        ];

        $expectedExistentSpots = [
            SpotFactory::create($this->event),
            SpotFactory::create($this->event),
            SpotFactory::create($this->event),
        ];

        $this
            ->spotImporter
            ->import(
                $this->event,
                $this->filename,
                'fr'
            )
            ->shouldBeCalled()
            ->willReturn($expectedSpotImport)
        ;

        $this
            ->spotRepository
            ->getAllByEvent($this->event)
            ->shouldBeCalled()
            ->willReturn($expectedExistentSpots)
        ;

        $this
            ->spotRepository
            ->removeBatchSpot($expectedExistentSpots, $this->event)
            ->shouldBeCalled();

        $this
            ->spotRepository
            ->add($expectedImportedDenormalizedSpot1->spot)
            ->shouldBeCalled()
        ;

        $this
            ->spotRepository
            ->add($expectedImportedDenormalizedSpot2->spot)
            ->shouldBeCalled()
        ;

        $this
            ->spotRepository
            ->add($expectedImportedDenormalizedSpot3->spot)
            ->shouldBeCalled()
        ;

        $this
            ->spotRepository
            ->add($expectedImportedDenormalizedSpot4->spot)
            ->shouldBeCalled()
        ;

        $this
            ->sheetRepository
            ->findByIds($sheetIds)
            ->shouldBeCalled()
            ->willReturn([$sheet1, $sheet2, $sheet3, $sheet4])
        ;

        $this
            ->sheetRepository
            ->set($sheet1)
            ->shouldBeCalled()
        ;

        $this
            ->sheetRepository
            ->set($sheet2)
            ->shouldBeCalled()
        ;

        $this
            ->sheetRepository
            ->set($sheet3)
            ->shouldBeCalled()
        ;

        $this
            ->sheetRepository
            ->set($sheet4)
            ->shouldBeCalled()
        ;

        $command = new SpotImportConfirm($this->event, $this->filename, 'fr');

        $handler = new SpotImportConfirmHandler(
            $this->spotImporter->reveal(),
            $this->spotRepository->reveal(),
            $this->sheetRepository->reveal()
        );

        $handler->handle($command);
    }
}
