<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class HappeningViewQuery
{
    /** @var User */
    public $user;

    /** @var Happening */
    public $happening;

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    public function __construct(User $user, Happening $happening, Event $event, $locale)
    {
        $this->user = $user;
        $this->happening = $happening;
        $this->event = $event;
        $this->locale = $locale;
    }
}
