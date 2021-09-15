<?php

namespace Proximum\Vimeet\Application\Query\Transactional\Mail;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class TransactionalMailListViewQuery implements Query
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
