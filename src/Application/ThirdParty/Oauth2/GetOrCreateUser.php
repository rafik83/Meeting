<?php

namespace Proximum\Vimeet\Application\ThirdParty\Oauth2;

use Proximum\Vimeet\Domain\Model\Event;

class GetOrCreateUser
{
    /** @var Event */
    private $event;

    /** @var string */
    private $locale;

    /** @var string */
    private $email;

    /** @var string|null */
    private $firstName;

    /** @var string|null */
    private $lastName;

    public function __construct(Event $event, string $locale, string $email, ?string $firstName, ?string $lastName)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }
}
