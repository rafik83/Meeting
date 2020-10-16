<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

class AbstractNotification
{
    const TYPE_QUESTIONS = 'questions';
    const TYPE_CHAT = 'chat';

    protected function getHappeningTopic(string $happeningId, string $type): string
    {
        return sprintf('https://vimeet.events/happening/%d/webinar/%s', $happeningId, $type);
    }

    public function getNetworkingTopic(int $eventId): string
    {
        return sprintf('https://vimeet.events/networking/%d', $eventId);
    }

    public function getUserTopic(int $eventId, int $userId): string
    {
        return sprintf('https://vimeet.events/event/%d/user/%d', $eventId, $userId);
    }
}
