<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Participant;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class GetUserParticipantInfos
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var string */
    public $locale;

    public function __construct(Event $event, User $user, string $locale)
    {
        $this->event = $event;
        $this->user = $user;
        $this->locale = $locale;
    }
}
