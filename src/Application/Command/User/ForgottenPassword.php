<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Domain\Model\Event;

class ForgottenPassword
{
    /** @var string */
    public $email;

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var bool */
    public $requestedByAdmin;

    public function __construct(Event $event, string $locale, bool $requestedByAdmin = false)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->requestedByAdmin = $requestedByAdmin;
    }
}
