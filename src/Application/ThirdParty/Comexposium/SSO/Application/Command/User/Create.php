<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\User;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Create implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $email;

    /** @var string */
    public $locale;

    public function __construct(Event $event, string $email, string $locale)
    {
        $this->event = $event;
        $this->email = $email;
        $this->locale = $locale;
    }
}
