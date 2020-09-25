<?php

namespace Proximum\Vimeet\Application\Adapter;


use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;

interface NotificationSubscriberInterface
{
    public function getUrl(): string;
    public function getHappeningSubscriberKey(Happening $happening, int $userId, array $types): string;
    public function getNetworkingSubscriberKey(Event $event, int $userId, array $types): string;
    public function getNotificationTopic(int $eventId): string;
}
