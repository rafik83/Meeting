<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

interface UserEventProtectedKeyPasswordGetterInterface
{
    public function getProtectedKeyPasswordByEventAndUser(Event $event, User $user): string;
}
