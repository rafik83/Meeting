<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

interface UserEventProtectedKeyGetterInterface
{
    public function getProtectedKeyByEventAndUser(Event $event, User $user): string;
}
