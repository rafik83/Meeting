<?php

namespace Proximum\Vimeet\Tests\Domain\Slot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Slot\SlotFilter;

class SlotPlus10minutesTest extends TestCase
{
    public function testFilteredSpot()
    {
        $dateTime = new \DateTime('2017-01-01 12:00:00');
        $slot1    = $this->prophesize(MeetingSlot::class);
        $slot2    = $this->prophesize(MeetingSlot::class);
        $slot3    = $this->prophesize(MeetingSlot::class);
        $slot4    = $this->prophesize(MeetingSlot::class);

        $slot1->getId()->willReturn(1);
        $slot2->getId()->willReturn(2);
        $slot3->getId()->willReturn(3);
        $slot4->getId()->willReturn(4);

        $slot1->getBegin()->willReturn(new \DateTime('2017-01-01 12:09:00'));
        $slot2->getBegin()->willReturn(new \DateTime('2017-01-01 12:10:00'));
        $slot3->getBegin()->willReturn(new \DateTime('2017-01-01 12:11:00'));
        $slot4->getBegin()->willReturn(new \DateTime('2017-01-01 12:12:00'));

        // Expected slots
        $expectedFilteredSlots = [1 => 2, 2 => 3, 3 => 4];

        $slotPlus10minutes = new SlotFilter($dateTime);

        $filteredSlots = $slotPlus10minutes->getFilteredSlots([
            $slot1->reveal(),
            $slot2->reveal(),
            $slot3->reveal(),
            $slot4->reveal(),
        ]);

        $this->assertEquals($expectedFilteredSlots, array_map(function ($slot) {
            return $slot->getId();
        }, $filteredSlots));
    }
}
