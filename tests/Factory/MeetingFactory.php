<?php

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingFactory
{
    /**
     * @param Sheet|null $fromSheetParameter
     * @param Sheet|null $toSheetParameter
     *
     * @return Meeting\Request
     */
    public static function createRequest(Sheet $fromSheetParameter = null, Sheet $toSheetParameter = null)
    {
        $event     = EventFactory::createEvent();
        $createdAt = new \DateTime();
        $user      = UserFactory::create();

        $fromSheet = null !== $fromSheetParameter ? $fromSheetParameter : SheetFactory::create($event);
        $toSheet   = null !== $toSheetParameter ? $toSheetParameter : SheetFactory::create($event);

        $request = new Meeting\Request(
            $fromSheet,
            [],
            $toSheet,
            [],
            $createdAt,
            $user,
            $event
        );

        return $request;
    }

    /**
     * @param Sheet|null $fromSheet
     * @param Sheet|null $toSheet
     * @param Event|null $event
     * @param array      $fromParticipant
     * @param array      $toParticipant
     *
     * @return Meeting
     */
    public static function createMeeting(
        Sheet $fromSheet = null,
        Sheet $toSheet = null,
        Event $event = null,
        array $fromParticipant = [],
        array $toParticipant = [],
        int $id = 1
    ) {
        $request   = self::createRequest($fromSheet, $toSheet);
        $slot      = SlotFactory::createSlot();
        $createdAt = new \DateTime();
        $spot      = SpotFactory::create();
        $event = (null === $event ? EventFactory::createEvent() : $event);

        $meeting = new Meeting(
            $request,
            $slot,
            $request->getFromSheet(),
            $fromParticipant,
            $request->getToSheet(),
            $toParticipant,
            $createdAt,
            $spot,
            $event,
            false,
            false
        );

        $reflection = new \ReflectionClass(Meeting::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($meeting, $id);
        $property->setAccessible(false);

        return $meeting;
    }
}
