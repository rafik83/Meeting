<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

use Proximum\Vimeet\Domain\Model\Event;

class AbstractNotification
{
    const TYPE_QUESTIONS = 'questions';
    const TYPE_CHAT = 'chat';
    // stream is used only when broadcast mode (hls) is enabled
    const TYPE_STREAM = 'stream';

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

    public function getCallVisioTopic(Event $event): string
    {
        return sprintf('https://vimeet.events/callvisio/%d', $event->getId());
    }
}
