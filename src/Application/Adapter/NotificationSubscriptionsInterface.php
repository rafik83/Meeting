<?php

namespace Proximum\Vimeet\Application\Adapter;


interface NotificationSubscriptionsInterface
{
    public function getSubscriptions(int $eventId, ?int $currentUserId, string $topicFilter = null): array;
    public function getStreamSubscriptionsCount(int $happeningId): int;
    public function hasUserSubscription(int $eventId, int $userId): bool;
}
