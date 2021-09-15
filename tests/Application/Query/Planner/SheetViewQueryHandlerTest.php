<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planner;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planner\SheetViewQuery;
use Proximum\Vimeet\Application\Query\Planner\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;
use Proximum\Vimeet\Domain\Planner\IndicatorCalculator;
use Proximum\Vimeet\Domain\Planner\IndicatorView;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SheetViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Data
        $event = EventFactory::createEvent();
        $type = new Type($event);
        $type2 = new Type($event);
        $typeView1 = new TypeView(1, 'title');
        $typeView2 = new TypeView(2, 'secondTitle');

        $sheet1  = SheetFactory::create($event, null, null, $type);
        $sheet2 = SheetFactory::create($event, null, null, $type);
        $sheet3 = SheetFactory::create($event, null, null, $type2);
        $sheet4 = SheetFactory::create($event, null, null, $type2);

        $indicator1 = new IndicatorView(1, 1, 1, 1, 1, 1, 1, null, null);
        $indicator2 = new IndicatorView(2, 2, 2, 2, 2, 2, 1, null, null);
        $indicator3 = new IndicatorView(3, 3, 3, 3, 3, 3, 1, null, null);
        $indicator4 = new IndicatorView(10, 3, 3, 3, 35, 3, 1, 8, null);

        // Reflection
        $reflection = new \ReflectionClass(Sheet::class);
        $reflectionType = new \ReflectionClass(Type::class);
        $property = $reflection->getProperty('id');
        $propertyType = $reflectionType->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet1, 1);
        $property->setValue($sheet2, 2);
        $property->setValue($sheet3, 3);
        $property->setValue($sheet4, 4);
        $property->setAccessible(false);
        $propertyType->setAccessible(true);
        $propertyType->setValue($type, 1);
        $propertyType->setValue($type2, 2);
        $propertyType->setAccessible(false);

        // Mock
        $sheetRepository     = $this->prophesize(SheetRepositoryInterface::class);
        $indicatorCalculator = $this->prophesize(IndicatorCalculator::class);

        $sheetRepository
            ->getSheetsInCatalogByEvent($event)
            ->shouldBeCalled()
            ->willReturn([$sheet1, $sheet2, $sheet3, $sheet4]);
        $indicatorCalculator->getIndicator($sheet1)->shouldBeCalled()->willReturn($indicator1);
        $indicatorCalculator->getIndicator($sheet2)->shouldBeCalled()->willReturn($indicator2);
        $indicatorCalculator->getIndicator($sheet3)->shouldBeCalled()->willReturn($indicator3);
        $indicatorCalculator->getIndicator($sheet4)->shouldBeCalled()->willReturn($indicator4);

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->countMeetingsOfEvent($event)->shouldBeCalled()->willReturn(
            [
                1 => ['countMeetings' => 2],
                2 => ['countMeetings' => 2],
                3 => ['countMeetings' => 12],
                4 => ['countMeetings' => 3],
            ]
        );

        // Handler
        $handler = new SheetViewQueryHandler(
            $sheetRepository->reveal(),
            $indicatorCalculator->reveal(),
            $meetingRepository->reveal()
        );
        $result = $handler->handle(
            new SheetViewQuery($event, [$typeView1, $typeView2], ExportSolutionType::SOLUTION_OPTIMIZE_MOVING_ALLOWED)
        );

        $expected = [
            new SheetView(1, $typeView1, 1, 2),
            new SheetView(2, $typeView1, 2, 2),
            new SheetView(3, $typeView2, 3, 12),
            new SheetView(4, $typeView2, 3, 24),
        ];

        $this->assertEquals($expected, $result);
    }
}
