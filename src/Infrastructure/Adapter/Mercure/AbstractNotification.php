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

    protected function getNotificationTopic(int $eventId, string $contextType, int $contextId)
    {
        return sprintf('https://vimeet.events/event/%d/notifications/%s/%d', $eventId, $contextType, $contextId);
    }
}
