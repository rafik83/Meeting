<?php

namespace Proximum\Vimeet\Domain\Repository\User\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\PresenceDate;

interface PresenceDateRepositoryInterface
{
    public function add(PresenceDate $presenceDate): void;

    public function remove(PresenceDate $presenceDate): void;

    public function getByUserAndEvent(User $user, Event $event): ?PresenceDate;
}
