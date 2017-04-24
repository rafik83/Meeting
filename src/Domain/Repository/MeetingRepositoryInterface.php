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
     * @param Event  $event
     *
     * @return Meeting[]
     */
    public function getAllByEvent(Event $event);

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
     * @param Event $event
     *
     * @return int[]
     */
    public function countMeetingsOfEvent(Event $event);

    /**
     * @param Participant $participant
     *
     * @return int
     */
    public function countByParticipant(Participant $participant);

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
