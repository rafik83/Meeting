<?php

namespace Proximum\Vimeet\Application\Query\Group\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class SheetViewQuery
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var string */
    public $locale;

    /**
     * SheetViewQuery constructor.
     *
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
