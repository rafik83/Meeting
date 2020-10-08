<?php

namespace Proximum\Vimeet\Application\Adapter;


use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

interface NotificationSubscriberInterface
{
    public function getUrl(): string;

    public function getHappeningSubscriberKey(Happening $happening, User $user, array $types): string;

    public function getNetworkingSubscriberKey(Event $event, User $user, $types): string;

    public function getNotificationTopic(int $eventId): string;

    public function getChatSessionTopic(int $userId): string;

    public function getEventSubscriberKey(Event $event, User $user): string;
}
