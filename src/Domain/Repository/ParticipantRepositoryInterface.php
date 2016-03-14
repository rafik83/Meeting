<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\ParticipantView;

interface ParticipantRepositoryInterface
{
    /**
     * @param Participant $participant
     */
    public function add(Participant $participant);

    /**
     * @param Participant $participant
     */
    public function delete(Participant $participant);

    /**
     * @param int $id
     *
     * @return Participant
     */
    public function findById($id);

    /**
     * @param Participant $participant
     */
    public function set(Participant $participant);

    /**
     * @param $userEmail
     * @param $eventId
     *
     * @return int
     */
    public function getLastParticipantIdForEventAndUser($userEmail, $eventId);

    /**
     * @param User  $user
     * @param Sheet $sheet
     *
     * @return Participant
     */
    public function getParticipantForUserAndSheet(User $user, Sheet $sheet);

    /**
     * @param int $eventId
     * @param int $userId
     *
     * @return array
     */
    public function getAllParticipantForUser($eventId, $userId);

    /**
     * @param int $sheetId
     *
     * @return ParticipantView[]
     */
    public function getParticipantViewsBySheet($sheetId);

    /**
     * @param Sheet $sheet
     *
     * @return array
     */
    public function getInactiveParticipantForSheet(Sheet $sheet);

    /**
     * @param Sheet   $sheet
     * @param Meeting $meeting
     *
     * @return Participant[]
     */
    public function findAvailableBySheetAndMeeting(Sheet $sheet, Meeting $meeting);
}
