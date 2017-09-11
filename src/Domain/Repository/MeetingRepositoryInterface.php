<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;

interface MeetingRepositoryInterface
{
    /**
     * @param Meeting $meeting
     */
    public function add(Meeting $meeting);

    /**
     * @param Meeting $meeting
     */
    public function set(Meeting $meeting);

    /**
     * @param Event  $event
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     *
     * @return PaginatedResult
     */
    public function getByEvent(Event $event, $page, $limit, $locale);

    /**
     * @param Event $event
     *
     * @return Meeting[]
     */
    public function getAllByEvent(Event $event): array;

    /**
     * @param Event $event
     *
     * @return Meeting[]
     */
    public function getNonBlockedSpotByEvent(Event $event): array;

    /**
     * @param Participant $participant
     *
     * @return Meeting[]
     */
    public function findByParticipant(Participant $participant);

    /**
     * @param Participant[] $participants
     *
     * @return Meeting[]
     */
    public function findByParticipants(array $participants);

    /**
     * @param Event  $event
     * @param User[] $users
     *
     * @return Meeting[]
     */
    public function findByEventAndUsers(Event $event, array $users);

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return Meeting[]
     */
    public function findByUserAndEvent(User $user, Event $event);

    /**
     * @param User  $user
     * @param Event $event
     * @param Sheet $exceptSheet
     *
     * @return Meeting[]
     */
    public function findByUserAndEventExceptSheet(User $user, Event $event, Sheet $exceptSheet);

    /**
     * @param Sheet $sheet
     *
     * @return Meeting[]
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param Event $event
     */
    public function deleteAll(Event $event);

    /**
     * @param Sheet $sheet
     */
    public function removeMeetingOfSheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countMeetingsFromSheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countMeetingsToSheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countMeetingsOfSheet(Sheet $sheet);

    /**
     * @param int[] $ids
     *
     * @return int[]
     */
    public function countMeetingsOfSheetByIds(array $ids);

    /**
     * @param Event $event
     *
     * @return int[]
     */
    public function countMeetingsOfEvent(Event $event);

    /**
     * @param Event $event
     * @param array $sheets
     *
     * @return array of ['countMeetings' => int, 'sheetId' => int]
     */
    public function countMeetingBySheets(Event $event, array $sheets): array;

    /**
     * @param Spot[] $spots
     *
     * @return array of ['countMeetings' => int, 'spotId' => int]
     */
    public function countMeetingsBySpots(array $spots): array;

    /**
     * @param Spot[] $spots
     *
     * @return int
     */
    public function countMeetingForSpots(array $spots): int;

    /**
     * @param Spot[]      $spots
     * @param MeetingSlot $meetingSlot
     *
     * @return int
     */
    public function countMeetingForSpotsAndSlot(array $spots, MeetingSlot $meetingSlot): int;

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countByEvent(Event $event): int;

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function hasMeeting(Event $event): bool;

    /**
     * @param Participant $participant
     *
     * @return int
     */
    public function countByParticipant(Participant $participant);

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return bool
     */
    public function hasMeetingForUserAndEvent(User $user, Event $event): bool;

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    public function hasScheduledMeetingByParticipant(Participant $participant);

    /**
     * @param Meeting $meeting
     */
    public function remove(Meeting $meeting);

    /**
     * @param MeetingSlot $meetingSlot
     *
     * @return bool
     */
    public function hasMeetingOnSlot(MeetingSlot $meetingSlot);

    /**
     * @param Spot        $spot
     * @param MeetingSlot $meetingSlot
     *
     * @return Meeting[]
     */
    public function findBySpotAndSlot(Spot $spot, MeetingSlot $meetingSlot);

    /**
     * @param Spot $spot
     *
     * @return Meeting[]
     */
    public function findBySpotWithSheets(Spot $spot);

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasScheduledMeeting(Sheet $sheet);

    /**
     * @param Event $event
     *
     * @return array
     *
     * Example of result:
     *  [
     *      1 => [
     *          'm'               => Meeting,
     *          'fromParticipant' => fromParticipant,
     *          'toParticipant'   => toParticipant,
     *          'meetingBegin'    => slot.begin,
     *          'meetingEnd'      => slot.end,
     *          'spotReference'   => spot.reference
     *      ]
     *  ]
     */
    public function getAllCompleteByEvent(Event $event);
}
