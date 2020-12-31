<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SMSNotificationCommand
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $verbalizedDiff;

    public function __construct(
        Event $event,
        User $user,
        Sheet $sheet,
        string $verbalizedDiff
    ) {
        $this->event = $event;
        $this->user = $user;
        $this->verbalizedDiff = $verbalizedDiff;
        $this->sheet = $sheet;
    }
}
