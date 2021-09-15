<?php

namespace Proximum\Vimeet\Domain\Repository\Token;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;

interface UserEventTokenRepositoryInterface
{
    /**
     * @param UserEventToken $userEventToken
     */
    public function add(UserEventToken $userEventToken);

    /**
     * @param UserEventToken $userEventToken
     */
    public function set(UserEventToken $userEventToken);

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $type
     *
     * @return UserEventToken|null
     */
    public function findByEventAndUserAndType(Event $event, User $user, $type);

    /**
     * @param Event  $event
     * @param string $type
     * @param User[] $users
     *
     * @return UserEventToken[]
     */
    public function getForEventTypeAndUsers(Event $event, string $type, array $users): array;

    public function findByTokenAndEventAndType(string $token, Event $event, string $type): ?UserEventToken;
}
