<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailabilityView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
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
        $participant = new Participant($sheet, $user, [], true);


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

        $handler = new SlotViewQueryHandler(
            $meetingSlotRepository->reveal(),
            $slotAvailability->reveal()
        );

        $handler->handle(new SlotViewQuery(
            $event,
            $day,
            $sheet,
            $participant,
            $happenings,
            $unavailabilities,
            $masses,
            $meetings
        ));
    }
}
