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
use Proximum\Vimeet\Application\Query\Planner\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Planner\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\View\Planner\Day;
use Proximum\Vimeet\Application\View\Planner\MeetingView;
use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Application\View\Planner\SpotView;
use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class MeetingViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Data
        $event      = EventFactory::createEvent();
        $typeView   = new TypeView(1, 'title');
        $sheet1     = SheetFactory::create($event);
        $sheet2     = SheetFactory::create($event);
        $sheet3     = SheetFactory::create($event);
        $sheet4     = SheetFactory::create($event);
        $sheetView  = new SheetView(1, $typeView, 2, 2);
        $sheetView2 = new SheetView(2, $typeView, 2, 2);
        $sheetView3 = new SheetView(3, $typeView, 2, 2);
        $sheets     = [$sheetView, $sheetView2, $sheetView3];

        $day       = new Day(1, 12, 10, 2016);
        $slotView  = new SlotView(1, 1, 10, 30, $day);
        $slotView2 = new SlotView(2, 2, 11, 0, $day);
        $slotView3 = new SlotView(3, 3, 11, 30, $day);
        $slots     = [$slotView, $slotView2, $slotView3];
        $spots     = [$this->prophesize(SpotView::class)->reveal()];

        $participant1     = ParticipantFactory::create($sheet1);
        $participant2     = ParticipantFactory::create($sheet1);
        $participant3     = ParticipantFactory::create($sheet2);
        $participant3->setVisio(true);
        $participant4     = ParticipantFactory::create($sheet3);
        $participant5     = ParticipantFactory::create($sheet3);

        $participantView  = new ParticipantView(1, 1, 'fullName1', $sheetView, [$slotView], false);
        $participantView2 = new ParticipantView(2, 2, 'fullName2', $sheetView, [], true);
        $participantView3 = new ParticipantView(3, 3, 'fullName3', $sheetView2, [$slotView2], true);
        $participantView4 = new ParticipantView(4, 4, 'fullName4', $sheetView3, [], false);
        $participantView5 = new ParticipantView(5, 5, 'fullName5', $sheetView3, [$slotView, $slotView2, $slotView3], true);
        $participants     = [
            $participantView,
            $participantView2,
            $participantView3,
            $participantView4,
            $participantView5,
        ];

        $creator  = UserFactory::create();
        $request1 = new Request($sheet1, [$participant1, $participant2], $sheet2, [$participant3], new \DateTime(), $creator, $event);
        $request2 = new Request($sheet3, [], $sheet1, [$participant1], new \DateTime(), $creator, $event);
        $request3 = new Request($sheet2, [], $sheet3, [], new \DateTime(), $creator, $event);
        $request4 = new Request($sheet4, [], $sheet3, [], new \DateTime(), $creator, $event); // Sheet not in catalog, should be escaped

        // Reflection
        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $propertySheet   = $reflectionSheet->getProperty('id');
        $propertySheet->setAccessible(true);
        $propertySheet->setValue($sheet1, 1);
        $propertySheet->setValue($sheet2, 2);
        $propertySheet->setValue($sheet3, 3);
        $propertySheet->setAccessible(false);

        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant1, 1);
        $property->setValue($participant2, 2);
        $property->setValue($participant3, 3);
        $property->setValue($participant4, 4);
        $property->setValue($participant5, 5);
        $property->setAccessible(false);

        $reflectionRequest = new \ReflectionClass(Request::class);
        $propertyRequest   = $reflectionRequest->getProperty('id');
        $propertyRequest->setAccessible(true);
        $propertyRequest->setValue($request1, 1);
        $propertyRequest->setValue($request2, 2);
        $propertyRequest->setValue($request3, 3);
        $propertyRequest->setValue($request4, 4);
        $propertyRequest->setAccessible(false);

        // Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->getAllAcceptedByEvent($event)->shouldBeCalled()->willReturn(
            [$request1, $request2, $request3, $request4]
        );

        // Handler
        $handler = new MeetingViewQueryHandler($requestRepository->reveal());
        $result  = $handler->handle(new MeetingViewQuery($event, $sheets, $participants, $slots, $spots, 'moving_allowed'));

        // Expected
        $expected = [
            new MeetingView(1, [$sheetView, $sheetView2], [$participantView, $participantView2, $participantView3], true),
            new MeetingView(2, [$sheetView3, $sheetView], [$participantView4, $participantView], false),
            new MeetingView(3, [$sheetView2, $sheetView3], [$participantView3, $participantView4], true),
        ];

        $this->assertEquals($expected, $result);
    }
}
