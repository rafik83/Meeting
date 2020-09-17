<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;


use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class UserCtaSubmenuViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    public function __construct(User $user, Event $event, string $locale, Sheet $sheet)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->sheet = $sheet;
        $this->user = $user;
    }
}

