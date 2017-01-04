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
use Proximum\Vimeet\Domain\Model\EventInterface;
use Proximum\Vimeet\Domain\Model\Happening;
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
     * @return Participant|null
     */
    public function getParticipantForUserAndSheet(User $user, Sheet $sheet);

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return array
     */
    public function getAllParticipantForUser(Event $event, User $user);

    /**
     * @param int            $userId
     * @param EventInterface $event
     *
     * @return Participant[]
     */
    public function getParticipantsByUserForEvent($userId, EventInterface $event);

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

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countByEnabledSheet(Event $event);

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return array
     */
    public function countByTypeWithEnabledSheet(Event $event, $locale);

    /**
     * @param Participant[]      $participants
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     *
     * @return Participant[]
     */
    public function getAvailableParticipants(array $participants, \DateTimeInterface $begin, \DateTimeInterface $end);

    /**
     * @param array   $participants
     * @param Meeting $meeting
     *
     * @return Participant[]
     */
    public function getAvailableParticipantsForMeeting(array $participants, Meeting $meeting);

    /**
     * @param array     $participants
     * @param Happening $happening
     *
     * @return Participant[]
     */
    public function getAvailableParticipantsForHappening(array $participants, Happening $happening);

    /**
     * @param Sheet     $sheet
     * @param Happening $happening
     *
     * @return Participant[]
     */
    public function getParticipantsForHappening(Sheet $sheet, Happening $happening);

    /**
     * @param int $id
     *
     * @return Participant[]
     */
    public function getParticipantsBySheetId($id);
}
