<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

use Predis\Client;
use Proximum\Vimeet\Application\Adapter\CachedNetworkingStatusInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;

class CachedNetworkingStatusAdapter implements CachedNetworkingStatusInterface
{
    public const IS_ONLINE_TTL = 60;

    private NotificationSubscriptionsInterface $notificationSubscriptions;
    private Client $client;

    public function __construct(
        NotificationSubscriptionsInterface $notificationSubscriptions,
        Client $client
    ) {
        $this->notificationSubscriptions = $notificationSubscriptions;
        $this->client = $client;
    }

    public function isOnline(int $eventId, int $userId): bool
    {
        $isOnline = $this->client->get($this->getIsUserOnlineKey($eventId, $userId));

        if ($isOnline === null) {
            $isOnline = $this->saveInRedisIsUserOnline($eventId, $userId);
        }

        return $isOnline;
    }

    private function getIsUserOnlineKey(int $eventId, int $userId): string
    {
        return sprintf('vimeet:%d:networking:%d:has-subscription', $eventId, $userId);
    }

    private function saveInRedisIsUserOnline(int $eventId, int $userId): bool
    {
        $hasUserSubscription = $this->notificationSubscriptions->hasUserSubscription($eventId, $userId);

        $key = $this->getIsUserOnlineKey($eventId, $userId);
        $this->client->set($key, $hasUserSubscription);
        $this->client->expire($key, self::IS_ONLINE_TTL);

        return $hasUserSubscription;
    }

}
