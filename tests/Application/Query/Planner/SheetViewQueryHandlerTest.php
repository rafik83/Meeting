<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
        $event     = EventFactory::createEvent();
        $type      = new Type($event);
        $type2     = new Type($event);
        $typeView  = new TypeView(1, 'title');
        $typeView2 = new TypeView(2, 'secondTitle');

        $sheet  = SheetFactory::create($event, null, null, $type);
        $sheet2 = SheetFactory::create($event, null, null, $type);
        $sheet3 = SheetFactory::create($event, null, null, $type2);

        $indicator  = new IndicatorView(1, 1, 1, 1, 1, 1, 1);
        $indicator2 = new IndicatorView(2, 2, 2, 2, 2, 2, 1);
        $indicator3 = new IndicatorView(3, 3, 3, 3, 3, 3, 1);

        // Reflection
        $reflection     = new \ReflectionClass(Sheet::class);
        $reflectionType = new \ReflectionClass(Type::class);
        $property       = $reflection->getProperty('id');
        $propertyType   = $reflectionType->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet, 1);
        $property->setValue($sheet2, 2);
        $property->setValue($sheet3, 3);
        $property->setAccessible(false);
        $propertyType->setAccessible(true);
        $propertyType->setValue($type, 1);
        $propertyType->setValue($type2, 2);
        $propertyType->setAccessible(false);

        // Mock
        $sheetRepository     = $this->prophesize(SheetRepositoryInterface::class);
        $indicatorCalculator = $this->prophesize(IndicatorCalculator::class);

        $sheetRepository
            ->getSheetsInCatalogWithAtLeastOneAcceptedRequestByEvent($event)
            ->shouldBeCalled()
            ->willReturn([$sheet, $sheet2, $sheet3]);
        $indicatorCalculator->getIndicator($sheet)->shouldBeCalled()->willReturn($indicator);
        $indicatorCalculator->getIndicator($sheet2)->shouldBeCalled()->willReturn($indicator2);
        $indicatorCalculator->getIndicator($sheet3)->shouldBeCalled()->willReturn($indicator3);

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->countMeetingsOfEvent($event)->shouldBeCalled()->willReturn(
            [
                1 => ['countMeetings' => 2],
                2 => ['countMeetings' => 2],
                3 => ['countMeetings' => 12],
            ]
        );

        // Handler
        $handler = new SheetViewQueryHandler(
            $sheetRepository->reveal(),
            $indicatorCalculator->reveal(),
            $meetingRepository->reveal()
        );
        $result  = $handler->handle(
            new SheetViewQuery($event, [$typeView, $typeView2], ExportSolutionType::SOLUTION_OPTIMIZE_MOVING_ALLOWED)
        );

        // Expected
        // The first sheet should be excluded as the possible meeting quantity is 0
        $expected = [
            new SheetView(2, $typeView, 2, 2),
            new SheetView(3, $typeView2, 3, 12),
        ];

        $this->assertEquals($expected, $result);
    }
}
