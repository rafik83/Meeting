<?php

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;

interface GroupRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return null|Group
     */
    public function getById($id): ?Group;

    /**
     * @param Event $event
     * @param User  $manager
     *
     * @return null|Group
     */
    public function getByEventAndManager(Event $event, User $manager): ?Group;

    /**
     * @param Event $event
     *
     * @return Group[]
     */
    public function getAllByEventOrderedByTitle(Event $event): array;

    /**
     * @param Group $group
     */
    public function add(Group $group);

    /**
     * @param Group $group
     */
    public function set(Group $group);

    /**
     * @param Event $event
     *
     * @return Group[]
     */
    public function getByEvent(Event $event): array;

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return Group|null
     */
    public function getByUserAndEvent(User $user, Event $event): ?Group;

    /**
     * @param Group $originGroup
     * @param Event $event
     *
     * @return null|Group
     */
    public function findDuplicatedGroupInEvent(Group $originGroup, Event $event): ?Group;
}
