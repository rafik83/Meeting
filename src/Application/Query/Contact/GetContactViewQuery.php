<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class GetContactViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var User */
    public $contact;

    /** @var string */
    public $locale;

    public function __construct(Event $event, User $contact, string $locale)
    {
        $this->event = $event;
        $this->contact = $contact;
        $this->locale = $locale;
    }
}
