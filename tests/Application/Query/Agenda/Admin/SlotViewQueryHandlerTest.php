<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Agenda\Slot\MeetingSlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\UnavailabilitySlotView;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailabilityView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SlotViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $start       = new \DateTime();
        $end         = new \DateTime();
        $day         = new Day($event, $start, $end);
        $locale      = 'fr';
        $user        = new User('john@doh.com', 'salt', 'password', $locale);
        $sheet       = SheetFactory::create($event, $user);
        $participant = ParticipantFactory::create($sheet, $user);


        $happenings       = [];
        $unavailabilities = [];
        $masses           = [];
        $meetings         = [];

        $slot = new MeetingSlot($event, new \DateTime(), new \DateTime(), false);
        $slotAvailabilityView = new SlotAvailabilityView(SlotAvailability::UNAVAILABILITY);

        // Mock
        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $slotAvailability      = $this->prophesize(SlotAvailability::class);

        $meetingSlotRepository->findByEventAndDay($event, $day)->shouldBeCalled()->willReturn([$slot]);

        $slotAvailability->preload(
            $happenings,
            $meetings,
            $unavailabilities,
            $masses
        )->shouldBeCalled();

        $slotAvailability->isAvailable($slot, $participant)->shouldBeCalled()->willReturn($slotAvailabilityView);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $sheetInfoGuesser->guessSheetTitle($sheet, $locale)->shouldNotBeCalled()->willReturn('toto');

        $handler = new SlotViewQueryHandler(
            $meetingSlotRepository->reveal(),
            $slotAvailability->reveal(),
            $sheetInfoGuesser->reveal()
        );

        $result = $handler->handle(new SlotViewQuery(
            $event,
            $day,
            $sheet,
            $participant,
            $happenings,
            $unavailabilities,
            $masses,
            $meetings
        ));

        $expected = [new UnavailabilitySlotView(
            $slot,
            SlotAvailability::UNAVAILABILITY
        )];

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithMeeting()
    {
        $event        = EventFactory::createEvent();
        $start        = new \DateTime('2016-10-12 10:00:00.000');
        $end          = new \DateTime('2016-10-12 18:00:00.000');
        $day          = new Day($event, $start, $end);
        $locale       = 'fr';
        $user         = new User('john@doh.com', 'salt', 'password', $locale);
        $user2        = new User('john@doh.com2', 'salt2', 'password2', $locale);
        $sheet        = SheetFactory::create($event, $user);
        $sheet2       = SheetFactory::create($event, $user2);

        $reflection  = new \ReflectionClass(Sheet::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet, 1);
        $property->setValue($sheet2, 2);
        $property->setAccessible(false);


        $participant  = ParticipantFactory::create($sheet, $user);
        $participant2 = ParticipantFactory::create($sheet2, $user2);
        $slot         = new MeetingSlot($event, new \DateTime('2016-10-12 11:00:00.000'), new \DateTime('2016-10-12 12:00:00.000'), false);


        $spot         = new Spot('ref', $event, 2, 3, 4, true);
        $reflectionS  = new \ReflectionClass(Spot::class);
        $propertyS    = $reflectionS->getProperty('id');
        $propertyS->setAccessible(true);
        $propertyS->setValue($spot, 10);
        $propertyS->setAccessible(false);


        $request = new Request($sheet, [], $sheet2, [$participant2], new \DateTime(), $user);
        $meeting = new Meeting($slot, $sheet, [$participant], $sheet2, [$participant2], new \DateTime(), $spot);
        $meeting->setRequest($request);

        $reflectionM  = new \ReflectionClass(Meeting::class);
        $propertyM = $reflectionM->getProperty('id');
        $propertyM->setAccessible(true);
        $propertyM->setValue($meeting, 1);
        $propertyM->setAccessible(false);

        $happenings       = [];
        $unavailabilities = [];
        $masses           = [];
        $meetings         = [];

        $slotAvailabilityView = new SlotAvailabilityView(SlotAvailability::MEETING_UNAVAILABILITY, $meeting);

        // Mock
        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $slotAvailability      = $this->prophesize(SlotAvailability::class);

        $meetingSlotRepository->findByEventAndDay($event, $day)->shouldBeCalled()->willReturn([$slot]);

        $slotAvailability->preload(
            $happenings,
            $meetings,
            $unavailabilities,
            $masses
        )->shouldBeCalled();

        $slotAvailability->isAvailable($slot, $participant)->shouldBeCalled()->willReturn($slotAvailabilityView);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $sheetInfoGuesser->guessSheetTitle($sheet2)->shouldBeCalled()->willReturn('sheetMetTitle');

        $handler = new SlotViewQueryHandler(
            $meetingSlotRepository->reveal(),
            $slotAvailability->reveal(),
            $sheetInfoGuesser->reveal()
        );

        $result = $handler->handle(new SlotViewQuery(
            $event,
            $day,
            $sheet,
            $participant,
            $happenings,
            $unavailabilities,
            $masses,
            $meetings
        ));

        $expected = [new MeetingSlotView(
            $slot,
            SlotAvailability::MEETING_UNAVAILABILITY,
            10,
            'ref',
            2,
            'sheetMetTitle',
            1,
            true
        )];

        $this->assertEquals($expected, $result);
    }
}
