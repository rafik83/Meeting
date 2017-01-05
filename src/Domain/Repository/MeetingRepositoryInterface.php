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
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

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
     * @param Participant $participant
     *
     * @return int
     */
    public function countByParticipant(Participant $participant);

    /**
     * @param Event $event
     *
     * @return Meeting[]
     */
    public function findByEvent(Event $event);
}
