<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;


use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class CustomLinkSubmenuViewQuery implements Query
{
    public Sheet $sheet;
    public User $user;
    public Event $event;
    public string $locale;

    public function __construct(Sheet $sheet, User $user, Event $event, string $locale)
    {
        $this->sheet = $sheet;
        $this->user = $user;
        $this->event = $event;
        $this->locale = $locale;
    }
}
