<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

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

        $fromSheet = $fromSheetParameter !== null ? $fromSheetParameter : SheetFactory::create($event);
        $toSheet   = $toSheetParameter !== null ? $toSheetParameter : SheetFactory::create($event);

        $request = new Meeting\Request(
            $fromSheet,
            [],
            $toSheet,
            [],
            $createdAt,
            $user
        );

        return $request;
    }

    /**
     * @return Meeting
     */
    public static function createMeeting()
    {
        $request   = self::createRequest();
        $slot      = SlotFactory::createSlot();
        $createdAt = new \DateTime();
        $spot      = SpotFactory::create();

        $meeting = new Meeting(
            $request,
            $slot,
            $request->getFromSheet(),
            [],
            $request->getToSheet(),
            [],
            $createdAt,
            $spot,
            false,
            false
        );

        return $meeting;
    }
}
