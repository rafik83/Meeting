<?php

namespace Proximum\Vimeet\Application\Adapter;


interface NotificationSubscriptionsInterface
{
    public function getSubscriptions(int $eventId, int $userId): array;
    public function getStreamSubscriptionsCount(int $happeningId): int;
}
