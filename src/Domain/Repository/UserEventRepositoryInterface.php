<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;

interface UserEventRepositoryInterface
{
    /**
     * @param UserEvent $userEvent
     */
    public function add(UserEvent $userEvent);

    /**
     * @param UserEvent $userEvent
     */
    public function set(UserEvent $userEvent);

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return UserEvent|null
     */
    public function getUserEvent(User $user, Event $event): ?UserEvent;
}
