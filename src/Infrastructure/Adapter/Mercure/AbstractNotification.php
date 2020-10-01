<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

class AbstractNotification
{
    const TYPE_QUESTIONS = 'questions';
    const TYPE_CHAT = 'chat';

    protected function getHappeningTopic(string $happeningId, string $type)
    {
        return sprintf('https://vimeet.events/happening/%d/webinar/%s', $happeningId, $type);
    }

    public function getNetworkingTopic(string $eventId, string $type)
    {
        return sprintf('https://vimeet.events/networking/%d/chat/%s', $eventId, $type);
    }

    public function getNotificationTopic(int $eventId): string
    {
        return sprintf('https://vimeet.events/event/%d/notifications', $eventId);
    }

    public function getUserConnectionTopic(int $eventId): string
    {
        return sprintf('https://vimeet.events/event/%d/user/connection', $eventId);
    }
}
