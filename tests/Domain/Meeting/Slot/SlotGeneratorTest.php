<?php

namespace Proximum\Vimeet\Tests\Domain\Meeting\Slot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Meeting\Slot\Recipe;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotGenerator;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SlotGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $event     = EventFactory::createEvent();
        $generator = new SlotGenerator();
        $actual    = $generator->generate($event, [
            new Recipe(new \DateTime('2016-06-15 09:00:00'), new \DateTime('2016-06-15 12:00:00'), 5, 25),
            new Recipe(new \DateTime('2016-06-15 14:00:00'), new \DateTime('2016-06-15 17:00:00'), 5, 25),
            new Recipe(new \DateTime('2016-06-16 09:00:00'), new \DateTime('2016-06-16 12:00:00'), 5, 25),
            new Recipe(new \DateTime('2016-06-16 14:00:00'), new \DateTime('2016-06-16 17:00:00'), 5, 25),
        ]);

        $expected = [
            // Morning 1
            new MeetingSlot($event, new \DateTime('2016-06-15 09:00:00'), new \DateTime('2016-06-15 09:25:00')),
            new MeetingSlot($event, new \DateTime('2016-06-15 09:30:00'), new \DateTime('2016-06-15 09:55:00')),
            new MeetingSlot($event, new \DateTime('2016-06-15 10:00:00'), new \DateTime('2016-06-15 10:25:00')),
            new MeetingSlot($event, new \DateTime('2016-06-15 10:30:00'), new \DateTime('2016-06-15 10:55:00')),
            new MeetingSlot($event, new \DateTime('2016-06-15 11:00:00'), new \DateTime('2016-06-15 11:25:00')),
            new MeetingSlot($event, new \DateTime('2016-06-15 11:30:00'), new \DateTime('2016-06-15 11:55:00')),
            // Afternoon 1
            new MeetingSlot($event, new \DateTime('2016-06-15 14:00:00'), new \DateTime('2016-06-15 14:25:00')),
            new MeetingSlot($event, new \DateTime('2016-06-15 14:30:00'), new \DateTime('2016-06-15 14:55:00')),
            new MeetingSlot($event, new \DateTime('2016-06-15 15:00:00'), new \DateTime('2016-06-15 15:25:00')),
            new MeetingSlot($event, new \DateTime('2016-06-15 15:30:00'), new \DateTime('2016-06-15 15:55:00')),
            new MeetingSlot($event, new \DateTime('2016-06-15 16:00:00'), new \DateTime('2016-06-15 16:25:00')),
            new MeetingSlot($event, new \DateTime('2016-06-15 16:30:00'), new \DateTime('2016-06-15 16:55:00')),
            // Morning 2
            new MeetingSlot($event, new \DateTime('2016-06-16 09:00:00'), new \DateTime('2016-06-16 09:25:00')),
            new MeetingSlot($event, new \DateTime('2016-06-16 09:30:00'), new \DateTime('2016-06-16 09:55:00')),
            new MeetingSlot($event, new \DateTime('2016-06-16 10:00:00'), new \DateTime('2016-06-16 10:25:00')),
            new MeetingSlot($event, new \DateTime('2016-06-16 10:30:00'), new \DateTime('2016-06-16 10:55:00')),
            new MeetingSlot($event, new \DateTime('2016-06-16 11:00:00'), new \DateTime('2016-06-16 11:25:00')),
            new MeetingSlot($event, new \DateTime('2016-06-16 11:30:00'), new \DateTime('2016-06-16 11:55:00')),
            // Afternoon 2
            new MeetingSlot($event, new \DateTime('2016-06-16 14:00:00'), new \DateTime('2016-06-16 14:25:00')),
            new MeetingSlot($event, new \DateTime('2016-06-16 14:30:00'), new \DateTime('2016-06-16 14:55:00')),
            new MeetingSlot($event, new \DateTime('2016-06-16 15:00:00'), new \DateTime('2016-06-16 15:25:00')),
            new MeetingSlot($event, new \DateTime('2016-06-16 15:30:00'), new \DateTime('2016-06-16 15:55:00')),
            new MeetingSlot($event, new \DateTime('2016-06-16 16:00:00'), new \DateTime('2016-06-16 16:25:00')),
            new MeetingSlot($event, new \DateTime('2016-06-16 16:30:00'), new \DateTime('2016-06-16 16:55:00')),
        ];

        $this->assertEquals($expected, $actual);
    }
}
