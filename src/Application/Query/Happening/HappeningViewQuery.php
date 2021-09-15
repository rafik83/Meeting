<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class HappeningViewQuery
{
    /** @var User */
    public $user;

    /** @var Happening */
    public $happening;

    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    public function __construct(
        User $user,
        Happening $happening,
        Event $event,
        string $locale
    ) {
        $this->user = $user;
        $this->happening = $happening;
        $this->event = $event;
        $this->locale = $locale;
    }
}
