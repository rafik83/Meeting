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

    /** @var string */
    public $origin;

    public function __construct(Event $event, User $user, User $contact, string $origin)
    {
        $this->event = $event;
        $this->user = $user;
        $this->contact = $contact;
        $this->origin = $origin;
    }
}
