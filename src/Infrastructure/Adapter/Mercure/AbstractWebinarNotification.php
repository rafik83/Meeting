<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

class AbstractWebinarNotification
{
    const TYPE_QUESTIONS = 'questions';

    protected function getTopic(string $happeningId, string $type)
    {
        return sprintf('https://vimeet.events/happening/%d/webinar/%s', $happeningId, $type);
    }
}
