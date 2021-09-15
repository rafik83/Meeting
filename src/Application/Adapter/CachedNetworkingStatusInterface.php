<?php

namespace Proximum\Vimeet\Application\Adapter;


interface CachedNetworkingStatusInterface
{
    public function isOnline(int $eventId, int $userId): bool;
}
