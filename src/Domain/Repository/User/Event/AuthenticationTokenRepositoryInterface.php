<?php

namespace Proximum\Vimeet\Domain\Repository\User\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\AuthenticationToken;

interface AuthenticationTokenRepositoryInterface
{
    public function add(AuthenticationToken $authenticationToken): void;

    public function set(AuthenticationToken $authenticationToken): void;

    public function findByEventAndUser(Event $event, User $user): ?AuthenticationToken;
}
