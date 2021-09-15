<?php

namespace Proximum\Vimeet\Application\View\Admin;

use Proximum\Vimeet\Domain\Model\Admin;

class AdminView
{
    /** @var int */
    public $id;

    /** @var string */
    public $lastName;

    /** @var string */
    public $firstName;

    /** @var string */
    public $email;

    /** @var string */
    public $role;

    /** @var string[] */
    public $events;

    public function __construct(
        int $id,
        string $lastName,
        string $firstName,
        string $email,
        string $role,
        array $events
    ) {
        $this->id = $id;
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->email = $email;
        $this->role = $role;
        $this->events = $events;
    }

    public function hasAllEvents(): bool
    {
        return $this->role === Admin::ROLE_SUPER_ADMIN && empty($this->events);
    }

    public function hasNoEvent(): bool
    {
        return empty($this->events);
    }

    public function isPartner(): bool
    {
        return $this->role === Admin::ROLE_PARTNER;
    }
}
