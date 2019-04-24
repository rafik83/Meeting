<?php

namespace Proximum\Vimeet\Application\Command\Contact;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class Add implements Command
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var User */
    public $contact;

    public function __construct(Event $event, User $user, User $contact)
    {
        $this->event = $event;
        $this->user = $user;
        $this->contact = $contact;
    }
}
