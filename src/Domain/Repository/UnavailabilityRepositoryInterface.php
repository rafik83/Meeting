<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;

interface UnavailabilityRepositoryInterface
{
    /**
     * @param Unavailability $unavailability
     */
    public function add(Unavailability $unavailability);

    /**
     * @param Unavailability $unavailability
     */
    public function set(Unavailability $unavailability);

    /**
     * @param Unavailability $unavailability
     */
    public function remove(Unavailability $unavailability);

    /**
     * @param Event $event
     *
     * @return Unavailability[]
     */
    public function getByEvent(Event $event);

    /**
     * @param Participant $participant
     *
     * @return int
     */
    public function countByParticipant(Participant $participant);

    /**
     * @deprecated Prefer use of findByUserAndEvent instead
     * @see findByUserAndEvent
     *
     * @param Participant $participant
     *
     * @return Unavailability[]
     */
    public function findByParticipant(Participant $participant);

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return Unavailability[]
     */
    public function findByUserAndEvent(User $user, Event $event);

    /**
     * @param User  $user
     * @param Event $event
     */
    public function removeSystemUnavailabilityForUserAndEvent(User $user, Event $event): void;

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return Unavailability[]
     */
    public function findByUserAndEventCreatedByUser(User $user, Event $event): array;

    /**
     * @param Participant[] $participants
     *
     * @return Unavailability[]
     */
    public function findByParticipants(array $participants);

    /**
     * @param Event  $event
     * @param User[] $users
     *
     * @return Unavailability[]
     */
    public function findByEventAndUsers(Event $event, array $users);

    /**
     * @param Sheet $sheet
     *
     * @return Unavailability[]
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param Unavailability $unavailability
     *
     * @return Unavailability[]
     */
    public function getOverlapUnavailabilities(Unavailability $unavailability);
}
