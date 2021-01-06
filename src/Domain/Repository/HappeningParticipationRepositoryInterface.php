<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

interface HappeningParticipationRepositoryInterface
{
    /**
     * @param HappeningParticipation $happeningParticipation
     */
    public function add(HappeningParticipation $happeningParticipation);

    /**
     * @param HappeningParticipation $happeningParticipation
     */
    public function remove(HappeningParticipation $happeningParticipation);

    /**
     * @return HappeningParticipation[]
     */
    public function findByUser(User $user, Event $event, bool $excludeDisabled, bool $onlyVisible = false): array;

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return bool
     */
    public function hasParticipationForUserAndEvent(User $user, Event $event): bool;

    /**
     * @param Event  $event
     * @param User[] $users
     *
     * @return HappeningParticipation[]
     */
    public function findByEventAndUsers(Event $event, array $users);

    /**
     * @deprecated
     * @see findByEventAndUsers
     *
     * @param Participant[] $participants
     * @param Event         $event
     *
     * @return HappeningParticipation[]
     */
    public function findByParticipants(array $participants, Event $event);

    /**
     * @param Happening $happening
     *
     * @return int
     */
    public function countParticipationByHappening(Happening $happening);

    /**
     * @param Event $event
     *
     * @return HappeningParticipation[]
     */
    public function getByEvent(Event $event);

    /**
     * @param Sheet $sheet
     *
     * @return HappeningParticipation[]
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param Event $event
     *
     * @return array
     */
    public function countParticipationByEvent(Event $event);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countParticipationsBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasParticipationsBySheet(Sheet $sheet);

    /**
     * @param Sheet       $sheet
     * @param Happening[] $happenings
     *
     * @return HappeningParticipation[]
     */
    public function getParticipationsForSheet(Sheet $sheet, array $happenings);

    /**
     * @param User      $user
     * @param Happening $happening
     */
    public function removeUserForHappening(User $user, Happening $happening);

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return int|null
     */
    public function checkAnyParticipation(User $user, Event $event);

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return int|null
     */
    public function countByUserAndEvent(User $user, Event $event): int;

    /**
     * @param HappeningParticipation $happeningParticipation
     */
    public function update(HappeningParticipation $happeningParticipation);

    /**
     * @param Happening $happening
     * @param User      $user
     *
     * @return HappeningParticipation|null
     */
    public function findByHappeningAndUser(Happening $happening, User $user);

    /**
     * @param Happening $happening
     *
     * @return HappeningParticipation[]
     */
    public function findByHappening(Happening $happening): array;

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return HappeningParticipation[]
     */
    public function findBySpeaker(User $user, Event $event): array;

    public function hasHappeningParticipant(Event $event): bool;
}
