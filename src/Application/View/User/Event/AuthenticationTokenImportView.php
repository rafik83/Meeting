<?php

namespace Proximum\Vimeet\Application\View\User\Event;

use Proximum\Vimeet\Domain\Model\Event;

class AuthenticationTokenImportView
{
    /** @var Event */
    public $event;

    /** @var string */
    public $email;

    /** @var string */
    public $token;

    /** @var null|\DateTimeInterface */
    public $expirationDate;

    public function __construct(Event $event, string $email, string $token, ?\DateTimeInterface $expirationDate = null)
    {
        $this->event = $event;
        $this->email = $email;
        $this->token = $token;
        $this->expirationDate = $expirationDate;
    }
}
