<?php

namespace Proximum\Vimeet\Application\Adapter;


use Proximum\Vimeet\Domain\Model\User;

interface NotificationSubscriptionsInterface
{
    public function getSubscriptions(string $topic, User $user): array;
}
