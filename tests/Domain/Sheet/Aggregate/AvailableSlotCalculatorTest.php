<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Sheet\Aggregate;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Aggregate\AvailableSlotCalculator;

class AvailableSlotCalculatorTest extends TestCase
{
    public function testCalculateAvailableSlotForSheet()
    {
        $event = $this->prophesize(Event::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);
        $participants = [
            $participant1->reveal(),
            $participant2->reveal(),
            $participant3->reveal()
        ];
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getParticipants()->willReturn(new ArrayCollection($participants));
        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot2 = $this->prophesize(MeetingSlot::class);
        $slot3 = $this->prophesize(MeetingSlot::class);
        $slot4 = $this->prophesize(MeetingSlot::class);
        $slot1->getId()->willReturn(1);
        $slot2->getId()->willReturn(2);
        $slot3->getId()->willReturn(3);
        $slot4->getId()->willReturn(4);


        // Mock
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $slotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);

        // Expected
        $slotRepository
            ->findAvailableSlotsByParticipants($event->reveal(), $participants)
            ->shouldBeCalled()
            ->willReturn([
                $slot1,
                $slot2,
                $slot3,
                $slot4,
            ])
        ;

        $sheet->setAvailableSlots([1, 2, 3, 4])->shouldBeCalled();
        $sheetRepository->set($sheet)->shouldBeCalled();

        $calculator = new AvailableSlotCalculator($slotRepository->reveal(), $sheetRepository->reveal());
        $calculator->calculateAvailableSlotForSheet($sheet->reveal());
    }

}
