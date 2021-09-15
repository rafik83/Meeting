<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class UserContactEvaluationStatsViewQuery implements Query
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
