<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planner;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planner\SpotViewQuery;
use Proximum\Vimeet\Application\Query\Planner\SpotViewQueryHandler;
use Proximum\Vimeet\Application\View\Planner\Day;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Application\View\Planner\SpotView;
use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SlotFactory;

class SpotViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Data
        $event = EventFactory::createEvent();
        $sheet  = SheetFactory::create($event);
        $sheet2 = SheetFactory::create($event);
        $sheet3 = SheetFactory::create($event);

        $type       = new TypeView(1, 'title');
        $sheetView  = new SheetView(1, $type, 2, 2);
        $sheetView2 = new SheetView(2, $type, 3, 3);
        $sheetView3 = new SheetView(3, $type, 4, 4);

        $spot = new Spot('ref1', $event, 2, 2, 2, true, 8, true);
        $spot2 = new Spot('ref2', $event, 3, 3, 3, true, 8, false);
        $spot3 = new Spot('ref3', $event, 4, 4, 4, true, 12, true);
        $spot4 = new Spot('ref4', $event, 5, 5, 5, true, 12, false);
        $spot->addSheet($sheet);
        $spot2->addSheet($sheet2);
        $spot2->addSheet($sheet3);

        // Reflection
        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $propertySheet   = $reflectionSheet->getProperty('id');
        $propertySheet->setAccessible(true);
        $propertySheet->setValue($sheet, 1);
        $propertySheet->setValue($sheet2, 2);
        $propertySheet->setValue($sheet3, 3);
        $propertySheet->setAccessible(false);

        $reflectionSpot = new \ReflectionClass(Spot::class);
        $propertySpot   = $reflectionSpot->getProperty('id');
        $propertySpot->setAccessible(true);
        $propertySpot->setValue($spot, 1);
        $propertySpot->setValue($spot2, 2);
        $propertySpot->setValue($spot3, 3);
        $propertySpot->setValue($spot4, 4);
        $propertySpot->setAccessible(false);

        // Mock
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->getActiveByEvent($event)->shouldBeCalled()->willReturn([$spot, $spot2, $spot3, $spot4]);

        // Handler
        $handler = new SpotViewQueryHandler($spotRepository->reveal());
        $result  = $handler->handle(new SpotViewQuery($event, [$sheetView, $sheetView2, $sheetView3], []));

        // Expected
        $expected = [
            new SpotView(1, true, 'ref1', 2, 2, [$sheetView], 8, []),
            new SpotView(2, false, 'ref2', 3, 3, [$sheetView2, $sheetView3], 8, []),
            new SpotView(3, true, 'ref3', 4, 4, [], 12, []),
            new SpotView(4, false, 'ref4', 5, 5, [], 12, []),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithUnavailability()
    {
        // Data
        $event = EventFactory::createEvent();
        $sheet  = SheetFactory::create($event);
        $sheet2 = SheetFactory::create($event);
        $sheet3 = SheetFactory::create($event);

        $type       = new TypeView(1, 'title');
        $sheetView  = new SheetView(1, $type, 2, 2);
        $sheetView2 = new SheetView(2, $type, 3, 3);
        $sheetView3 = new SheetView(3, $type, 4, 4);

        $spot = new Spot('ref1', $event, 2, 2, 2, true, 8, true);
        $spot2 = new Spot('ref2', $event, 3, 3, 3, true, 8, false);
        $spot3 = new Spot('ref3', $event, 4, 4, 4, true, 12, true);
        $spot4 = new Spot('ref4', $event, 5, 5, 5, true, 12, false);
        $spot->addSheet($sheet);
        $spot2->addSheet($sheet2);
        $spot2->addSheet($sheet3);

        $slot  = SlotFactory::createSlot(1, $event);
        $slot2 = SlotFactory::createSlot(2, $event);
        $unavailability  = new SpotUnavailability($slot, $spot);
        $unavailability2 = new SpotUnavailability($slot2, $spot2);
        $spot->addSpotUnavailability([$unavailability]);
        $spot2->addSpotUnavailability([$unavailability2]);

        $day       = new Day(1, 1, 1, 1);
        $slotView  = new SlotView(1, 1, 1, 30, $day);
        $slotView2 = new SlotView(2, 2, 2, 30, $day);

        // Reflection
        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $propertySheet   = $reflectionSheet->getProperty('id');
        $propertySheet->setAccessible(true);
        $propertySheet->setValue($sheet, 1);
        $propertySheet->setValue($sheet2, 2);
        $propertySheet->setValue($sheet3, 3);
        $propertySheet->setAccessible(false);

        $reflectionSpot = new \ReflectionClass(Spot::class);
        $propertySpot   = $reflectionSpot->getProperty('id');
        $propertySpot->setAccessible(true);
        $propertySpot->setValue($spot, 1);
        $propertySpot->setValue($spot2, 2);
        $propertySpot->setValue($spot3, 3);
        $propertySpot->setValue($spot4, 4);
        $propertySpot->setAccessible(false);

        $reflectionSlot = new \ReflectionClass(MeetingSlot::class);
        $propertySlot   = $reflectionSlot->getProperty('id');
        $propertySlot->setAccessible(true);
        $propertySlot->setValue($slot, 1);
        $propertySlot->setValue($slot2, 2);
        $propertySlot->setAccessible(false);

        // Mock
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->getActiveByEvent($event)->shouldBeCalled()->willReturn([$spot, $spot2, $spot3, $spot4]);

        // Handler
        $handler = new SpotViewQueryHandler($spotRepository->reveal());
        $result  = $handler->handle(new SpotViewQuery($event, [$sheetView, $sheetView2, $sheetView3], [$slotView, $slotView2]));

        // Expected
        $expected = [
            new SpotView(1, true, 'ref1', 2, 2, [$sheetView], 8, [$slotView]),
            new SpotView(2, false, 'ref2', 3, 3, [$sheetView2, $sheetView3], 8, [$slotView2]),
            new SpotView(3, true, 'ref3', 4, 4, [], 12, []),
            new SpotView(4, false, 'ref4', 5, 5, [], 12, []),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithoutSheetInCatalog()
    {
        // Data
        $event = EventFactory::createEvent();
        $sheet  = SheetFactory::create($event);
        $sheet2 = SheetFactory::create($event);
        $sheet3 = SheetFactory::create($event);

        $type       = new TypeView(1, 'title');
        $sheetView2 = new SheetView(2, $type, 3, 3);
        $sheetView3 = new SheetView(3, $type, 4, 4);

        $spot = new Spot('ref1', $event, 2, 2, 2, true, 8, true);
        $spot2 = new Spot('ref2', $event, 3, 3, 3, true, 8, false);
        $spot3 = new Spot('ref3', $event, 4, 4, 4, true, 12, true);
        $spot4 = new Spot('ref4', $event, 5, 5, 5, true, 12, false);
        $spot->addSheet($sheet);
        $spot2->addSheet($sheet2);
        $spot2->addSheet($sheet3);

        // Reflection
        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $propertySheet   = $reflectionSheet->getProperty('id');
        $propertySheet->setAccessible(true);
        $propertySheet->setValue($sheet, 1);
        $propertySheet->setValue($sheet2, 2);
        $propertySheet->setValue($sheet3, 3);
        $propertySheet->setAccessible(false);

        $reflectionSpot = new \ReflectionClass(Spot::class);
        $propertySpot   = $reflectionSpot->getProperty('id');
        $propertySpot->setAccessible(true);
        $propertySpot->setValue($spot, 1);
        $propertySpot->setValue($spot2, 2);
        $propertySpot->setValue($spot3, 3);
        $propertySpot->setValue($spot4, 4);
        $propertySpot->setAccessible(false);

        // Mock
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->getActiveByEvent($event)->shouldBeCalled()->willReturn([$spot, $spot2, $spot3, $spot4]);

        // Handler
        $handler = new SpotViewQueryHandler($spotRepository->reveal());
        $result  = $handler->handle(new SpotViewQuery($event, [$sheetView2, $sheetView3], []));

        // Expected
        $expected = [
            new SpotView(2, false, 'ref2', 3, 3, [$sheetView2, $sheetView3], 8, []),
            new SpotView(3, true, 'ref3', 4, 4, [], 12, []),
            new SpotView(4, false, 'ref4', 5, 5, [], 12, []),
        ];

        $this->assertEquals($expected, $result);
    }
}
