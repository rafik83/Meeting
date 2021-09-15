<?php

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class SlotFactory
{
    /**
     * @param int                $id
     * @param Event              $event
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param bool               $locked
     *
     * @return MeetingSlot
     */
    public static function createSlot(
        $id = null,
        Event $event = null,
        \DateTimeInterface $begin = null,
        \DateTimeInterface $end = null,
        $locked = false
    ) {
        $slot = new MeetingSlot(
            null !== $event ? $event : EventFactory::createEvent(),
            null !== $begin ? $begin : new \DateTime(),
            null !== $end ? $end : new \DateTime(),
            $locked
        );

        if (null !== $id) {
            $reflection = new \ReflectionClass(MeetingSlot::class);

            $property = $reflection->getProperty('id');
            $property->setAccessible(true);
            $property->setValue($slot, $id);
            $property->setAccessible(false);
        }

        return $slot;
    }
}
