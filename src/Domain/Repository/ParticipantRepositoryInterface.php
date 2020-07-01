<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
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
     * @param Event $event
     *
     * @return Participant[]
     */
    public function findByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Participant[]
     */
    public function findByEventAndInCatalog(Event $event);

    /**
     * @param Event $event
     * @param int[] $sheetIds
     *
     * @return Participant[]
     */
    public function getByEventAndSheetIds(Event $event, array $sheetIds): array;

    /**
     * @param Event  $event
     * @param int[]  $sheetIds
     * @param string $locale
     *
     * @return Participant[]
     */
    public function getByEventAndSheetIdsAndLocale(Event $event, array $sheetIds, $locale): array;

    /**
     * @param Event $event
     * @param Mass  $mass
     *
     * @return Participant[]
     */
    public function findByEventWithoutDispatch(Event $event, Mass $mass);

    /**
     * @param int $id
     *
     * @return Participant
     */
    public function findById($id);

    /**
     * @param array $ids array of participant ids
     *
     * @return Participant[]
     */
    public function findByIds(array $ids);

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
     * @return Participant[]
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
     * @param Meeting|null       $exceptedMeeting
     * @param Happening|null     $exceptedHappening
     * @param bool               $exceptAllUnavailabilities
     *
     * @return Participant[]
     */
    public function getAvailableParticipants(
        array $participants,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        Meeting $exceptedMeeting = null,
        Happening $exceptedHappening = null,
        $exceptAllUnavailabilities = false
    );

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

    /**
     * @param int[] $ids
     *
     * @return Participant[]
     */
    public function getParticipantsBySheetIds(array $ids);

    /**
     * @param array
     *
     * @return Participant[]
     */
    public function getParticipantsBySheetIdsWithSheetAndTypeHydrated(array $ids);

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Participant[]
     */
    public function getParticipantsByEvent(Event $event, $locale);

    public function getParticipantsFromEnabledSheetsByEvent(Event $event,string  $locale): array;

    /**
     * @param Participant[]      $participants
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     *
     * @return Participant[]
     */
    public function getParticipantsWithoutMeetingAndHappening(
        array $participants,
        \DateTimeInterface $begin,
        \DateTimeInterface $end
    );

    public function isAvailableForMeeting(array $participants, Meeting $meeting): bool;

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countParticipantBySheet(Sheet $sheet);

    /**
     * @param Group $group
     *
     * @return Participant[]
     */
    public function findByGroup(Group $group);

    /**
     * @param User  $user
     * @param Event $currentEvent
     *
     * @return null|Participant
     */
    public function getLastEventParticipation(User $user, Event $currentEvent): ?Participant;

    /**
     * @param Event $event
     *
     * @return array of participant email with format
     *               [
     *               0 => ['email' => 'email0@example.net'],
     *               1 => ['email' => 'email1@example.net'],
     *               ]
     */
    public function getParticipantEmailsForEvent(Event $event): array;

    public function getProductIdsOfUserForEvent(User $user, Event $event): array;
}
