<?php

namespace Proximum\Vimeet\Domain\Repository\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;

interface MassAssignmentRepositoryInterface
{
    /**
     * @param MassAssignment $massAssignment
     */
    public function add(MassAssignment $massAssignment);

    /**
     * @param Mass        $mass
     * @param Participant $participant
     *
     * @return MassAssignment|null
     */
    public function find(Mass $mass, Participant $participant);

    /**
     * @param Event $event
     *
     * @return MassAssignment[]
     */
    public function findByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return MassAssignment[]
     */
    public function findEnabledByEvent(Event $event);

    /**
     * @param MassAssignment $massAssignment
     */
    public function set(MassAssignment $massAssignment);

    /**
     * @param Sheet $sheet
     *
     * @return MassAssignment[]
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param Participant $participant
     *
     * @return MassAssignment[]
     */
    public function findByParticipant(Participant $participant);

    /**
     * @deprecated Prefer use findEnabledByUserAndEvent instead
     * @see findEnabledByUserAndEvent
     *
     * @param Participant $participant
     *
     * @return MassAssignment[]
     */
    public function findEnabledByParticipant(Participant $participant);

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return MassAssignment[]
     */
    public function findEnabledByUserAndEvent(User $user, Event $event);

    /**
     * @deprecated
     * @see findEnabledByEventAndUsers
     *
     * @param Participant[] $participants
     *
     * @return MassAssignment[]
     */
    public function findEnabledByParticipants(array $participants);

    /**
     * @param Event  $event
     * @param User[] $users
     *
     * @return MassAssignment[]
     */
    public function findEnabledByEventAndUsers(Event $event, array $users);

    /**
     * @param $user
     * @param Mass $mass
     */
    public function removeByUserAndMass($user, Mass $mass);
}
