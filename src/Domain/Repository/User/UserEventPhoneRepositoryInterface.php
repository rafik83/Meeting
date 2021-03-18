<?php

namespace Proximum\Vimeet\Domain\Repository\User;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;

interface UserEventPhoneRepositoryInterface
{
    /**
     * @param UserEventPhone $userEventPhone
     */
    public function add(UserEventPhone $userEventPhone);

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return UserEventPhone|null
     */
    public function find(User $user, Event $event);

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return UserEventPhone|null
     */
    public function findValidated(User $user, Event $event);

    /**
     * @param UserEventPhone $userEventPhone
     */
    public function set(UserEventPhone $userEventPhone);

    /**
     * @param User  $user
     * @param Event $event
     */
    public function remove(User $user, Event $event);

    /**
     * @param array $blackList
     */
    public function setIntoBlackList(array $blackList);

    /**
     * @param array $blackList
     */
    public function unsetFromBlackList(array $blackList);

    /**
     * @param Event $event
     * @param int[] $usersId
     *
     * @return UserEventPhone[]
     */
    public function findValidatedByEventAndUsers(Event $event, array $usersId): array;
}
