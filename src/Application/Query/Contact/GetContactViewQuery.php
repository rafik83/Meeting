<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class GetContactViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $userSheet;

    /** @var User */
    public $contact;

    /** @var string */
    public $locale;

    public function __construct(Event $event, Sheet $userSheet, User $contact, string $locale)
    {
        $this->event = $event;
        $this->userSheet = $userSheet;
        $this->contact = $contact;
        $this->locale = $locale;
    }
}
