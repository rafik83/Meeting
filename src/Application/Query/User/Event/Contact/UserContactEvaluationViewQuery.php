<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class UserContactEvaluationViewQuery implements Query
{
    public Event $event;
    public string $locale;

    public function __construct(Event $event, string $locale)
    {
        $this->event = $event;
        $this->locale = $locale;
    }
}
