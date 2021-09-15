<?php

namespace Proximum\Vimeet\Application\Query\RegistrationPath;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class EventRegistrationPathQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    public function __construct(Event $event, string $locale)
    {
        $this->event = $event;
        $this->locale = $locale;
    }
}
