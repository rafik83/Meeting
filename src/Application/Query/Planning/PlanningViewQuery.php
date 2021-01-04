<?php

namespace Proximum\Vimeet\Application\Query\Planning;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class PlanningViewQuery
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var User */
    public $user;

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $locale
     */
    public function __construct(Event $event, User $user, $locale)
    {
        $this->event  = $event;
        $this->user   = $user;
        $this->locale = $locale;
    }
}
